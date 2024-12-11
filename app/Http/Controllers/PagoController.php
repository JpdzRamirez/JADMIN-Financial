<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Cuenta;
use App\Models\Cuota;
use App\Models\Extra;
use App\Models\Factura;
use App\Models\FacturasxRecibo;
use App\Models\FormaPago;
use App\Models\Movimiento;
use App\Models\Nota;
use App\Models\NtaContabilidad;
use App\Models\Pago;
use App\Models\Pagos_Cuotas;
use App\Models\Recibo;
use App\Models\Retefuente;
use App\Models\Reteica;
use App\Models\Reteiva;
use App\Models\TerceroJADMIN;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Luecano\NumeroALetras\NumeroALetras;

class PagoController extends Controller
{
    public function pagosCliente()
    {
        $pagos = Pago::with('cuota.credito')->where('users_id', Auth::user()->id)->orderBy('id', 'desc')->paginate(10);

        return view('pagos.cliente', compact('pagos'));
    }

    public function pagosRealizados()
    {
        $pagos = Pago::with(['cliente', 'credito.placa', 'recibo'])->orderBy('id', 'desc')->paginate(10);

        return view('pagos.lista', compact('pagos'));
    }

    public function registrarPagoView(Request $request)
    {
        if ($request->filled('identificacion')) {
            $tercero = TerceroJADMIN::with('usuario')->where('documento', $request->input('identificacion'))->first();
            if ($tercero != null) {
                if ($tercero->usuario != null) {
                    $creditos = Credito::with(['cuotas' => function ($q) {
                        $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
                    }])->whereHas('cuotas', function ($q) {
                        $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
                    })->where('users_id', $tercero->usuario->id)->get();
                } else {
                    $creditos = [];
                }
                $facturas = Factura::where('tipo', 'Venta')->where('cruzada', '0')->where('terceros_id', $tercero->id)->doesnthave('credito')->get();
                $busq = true;
            } else {
                return redirect('pagos/registrar')->with('cliente', 'El cliente con número de identificación ' . $request->input('identificacion') . ' no ha sido encontrado');
            }
        } else if ($request->filled('placa')) {
            $creditos = Credito::with(['cuotas' => function ($q) {
                $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
            }, 'cliente.tercero'])->whereHas('cuotas', function ($q) {
                $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
            })->where('placas', $request->input('placa'))->get();
            //$placa = Placa::with('cliente')->where('placa', $request->input('placa'))->has('creditos')->first();
            $facturas = Factura::where('tipo', 'Venta')->where('cruzada', '0')->where('placa', $request->input('placa'))->doesnthave('credito')->get();
            /*if($placa != null){
                $creditos = Credito::with(['cuotas'=>function($q){$q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');}])->whereHas('cuotas', function($q){$q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');})->where('placas_id', $placa->id)->get();
                $tercero = $placa->cliente->tercero;
            }else{
                $creditos = [];
                $tercero = null;
            } */
            if (count($creditos) > 0) {
                $tercero = $creditos[0]->cliente->tercero;
            } else {
                $tercero = null;
            }
            $busq = true;
        } else {
            return view('pagos.registrar');
        }

        return view('pagos.registrar', compact('tercero', 'creditos', 'facturas', 'busq'));
    }

    public function pagarCuotaView($cuota)
    {
        $cuota = Cuota::with(['credito' => function ($q) {
            $q->select('id', 'users_id')->with('cliente');
        }])->find($cuota);

        if ($cuota->estado == "Vigente" || $cuota->estado == "Vencida") {
            $formas = FormaPago::get();
            return view('pagos.pagar', compact('cuota', 'formas'));
        } else {
            return view('pagos.respuesta', ["respuesta" => "error"]);
        }
    }

    public function PagarCreditoView($credito)
    {
        $credito = Credito::with(['cliente', 'cuotas' => function ($q) {
            $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
        }])->whereHas('cuotas', function ($q) {
            $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
        })->find($credito);
        $deuda = 0;
        $mora = 0;
        $insoluto = 0;
        $interes = 0;
        foreach ($credito->cuotas as $cuota) {
            if ($cuota->estado == "Vencida") {
                $mora = $mora + $cuota->saldo_mora;
                $interes = $interes + $cuota->saldo_interes;
                $insoluto = $insoluto + $cuota->saldo_capital;
            } elseif ($cuota->estado == "Vigente") {
                $interes = $interes + $cuota->saldo_interes;
                if ($cuota->descripcion == null) {
                    $insoluto = $insoluto + $cuota->saldo_capital;
                }
            } elseif ($cuota->estado == "Pendiente") {
                $insoluto = $insoluto + $cuota->saldo_capital;
            }
        }

        $credito->insoluto = $insoluto;
        $credito->mora = $mora;
        $credito->interes = $interes;
        $credito->deuda = $insoluto + $mora + $interes;
        $formas = FormaPago::get();

        return view('pagos.abono', compact('credito', 'formas'));
    }

    public function pagarCredito(Request $request)
    {
        $credito = Credito::with(['tipos', 'cliente', 'factura', 'cuotas' => function ($q) {
            $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
        }])->whereHas('cuotas', function ($q) {
            $q->where('estado', 'Vigente')->orWhere('estado', 'Vencida')->orWhere('estado', 'Pendiente');
        })->find($request->input('credito'));
        $forma = FormaPago::with('cuenta')->find($request->input('forma'));
        $cliente = User::find($request->input('cliente'));

        $fecha = Carbon::now();
        $descuento = floatval($request->input('descuento'));
        $pagado = floatval($request->input('pago')) - $descuento;

        $recibo = new Recibo();
        $ultimo = Recibo::where('prefijo', 'RC1')->where('year', $fecha->year)->where('fecha', '>=', '2023-01-05')->orderBy('numero', 'desc')->first();
        if ($ultimo != null) {
            $recibo->numero = $ultimo->numero + 1;
        } else {
            $recibo->numero = 1;
        }
        $recibo->prefijo = "RC1";
        $recibo->fecha = $fecha;
        $recibo->year = $fecha->year;
        $recibo->valor = $pagado;
        $recibo->save();

        $facxrec = new FacturasxRecibo();
        $facxrec->facturas_id = $credito->factura->id;
        $facxrec->recibos_id = $recibo->id;
        $facxrec->abono = $recibo->valor;

        $ctmora = Cuenta::find(108);
        $ctinteres = Cuenta::find(107);
        $ctcreditos = Cuenta::find(212);
        if(count($credito->tipos) == 1){
            if($credito->tipos[0]->id == 4 && Carbon::parse($credito->fecha_prestamo)->gt(Carbon::parse("2023-04-23"))){
                $ctcreditos = Cuenta::find(233);
            }else if($credito->tipos[0]->id > 2 && Carbon::parse($credito->fecha_prestamo)->gt(Carbon::parse("2023-05-06"))){
                $ctcreditos = Cuenta::find(233);
            }
        }
        $nfact = count($credito->cuotas);

        $pago = new Pago();
        $pago->fecha = Carbon::now();
        $pago->valor = $pagado;
        $pago->descuento = $descuento;
        $pago->users_id = $cliente->id;
        $pago->observaciones = $request->input('observaciones');
        $pago->creditos_id = $credito->id;
        $pago->formas_pago_id = $forma->id;
        $pago->recibos_id = $recibo->id;
        $pago->save();

        $tmora = 0;
        $tinteres = 0;
        $tcapital = 0;

        $ultima = null;

        for ($i = 0; $i < $nfact; $i++) {
            if ($credito->cuotas[$i]->estado == "Vencida") {
                $pagoCuota = new Pagos_Cuotas();
                $pagoCuota->interes = $credito->cuotas[$i]->saldo_interes;
                $pagoCuota->capital = $credito->cuotas[$i]->saldo_capital;
                $pagoCuota->mora = $credito->cuotas[$i]->saldo_mora;
                $pagoCuota->valor = $pagoCuota->mora + $pagoCuota->interes + $pagoCuota->capital;

                $tmora = $tmora + $credito->cuotas[$i]->saldo_mora;
                $tinteres = $tinteres + $credito->cuotas[$i]->saldo_interes;
                $tcapital = $tcapital + $credito->cuotas[$i]->saldo_capital;
                $credito->cuotas[$i]->saldo_capital = 0;
                $credito->cuotas[$i]->saldo_interes = 0;
                $credito->cuotas[$i]->interes_mora = $credito->cuotas[$i]->interes_mora + $credito->cuotas[$i]->saldo_mora;
                $credito->cuotas[$i]->saldo_mora = 0;
                $credito->cuotas[$i]->estado = "Pagada";
                $credito->cuotas[$i]->save();

                $pagoCuota->pagos_id = $pago->id;
                $pagoCuota->cuotas_id = $credito->cuotas[$i]->id;
                $pagoCuota->save();
            } elseif ($credito->cuotas[$i]->estado == "Vigente") {
                $pagoCuota = new Pagos_Cuotas();
                $pagoCuota->interes = $credito->cuotas[$i]->saldo_interes;
                $pagoCuota->capital = $credito->cuotas[$i]->saldo_insoluto;
                $pagoCuota->valor = $pagoCuota->interes + $pagoCuota->capital;

                $tinteres = $tinteres + $credito->cuotas[$i]->saldo_interes;
                $tcapital = $tcapital + $credito->cuotas[$i]->saldo_capital;
                $credito->cuotas[$i]->abono_capital =  $credito->cuotas[$i]->saldo_insoluto;
                $credito->cuotas[$i]->saldo_capital = 0;
                $credito->cuotas[$i]->saldo_interes = 0;
                $credito->cuotas[$i]->estado = "Pagada";
                $credito->cuotas[$i]->save();

                $pagoCuota->pagos_id = $pago->id;
                $pagoCuota->cuotas_id = $credito->cuotas[$i]->id;
                $pagoCuota->save();
                $ultima = $credito->cuotas[$i]->ncuota;
            } elseif ($credito->cuotas[$i]->estado == "Pendiente") {
                $pagoCuota = new Pagos_Cuotas();
                $pagoCuota->capital = $credito->cuotas[$i]->saldo_capital;
                $pagoCuota->valor = $pagoCuota->capital;

                $tcapital = $tcapital + $credito->cuotas[$i]->saldo_capital;

                $pagoCuota->pagos_id = $pago->id;
                $pagoCuota->cuotas_id = $credito->cuotas[$i]->id;
                $pagoCuota->save();
            }
        }

        if ($ultima != null) {
            Cuota::where('creditos_id', $credito->id)->where('ncuota', '>', $ultima)->delete();
        } else {
            if ($credito->cuotas[0]->estado == "Pendiente") {
                Cuota::where('creditos_id', $credito->id)->delete();
            }
        }

        if ($tmora > 0) {
            $facxrec->mora = $tmora;
            $movimiento = new Movimiento();
            $movimiento->fecha = $fecha;
            $movimiento->valor = $tmora;
            $movimiento->naturaleza = "Crédito";
            $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " Intereses de mora";
            $movimiento->cuentas_id = $ctmora->id;
            $movimiento->recibos_id = $recibo->id;
            $movimiento->terceros_id = $cliente->terceros_id;
            $movimiento->save();
        }

        if ($tinteres > 0) {
            $movimiento = new Movimiento();
            $movimiento->fecha = $fecha;
            $movimiento->valor = $tinteres;
            $movimiento->naturaleza = "Crédito";
            $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero .  " " . $ctinteres->nombre;
            $movimiento->cuentas_id = $ctinteres->id;
            $movimiento->recibos_id = $recibo->id;
            $movimiento->terceros_id = $cliente->terceros_id;
            $movimiento->save();
        }

        if ($tcapital > 0) {
            $movimiento = new Movimiento();
            $movimiento->fecha = $fecha;
            $movimiento->valor = $tcapital;
            $movimiento->naturaleza = "Crédito";
            $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $ctcreditos->nombre;;
            $movimiento->cuentas_id = $ctcreditos->id;
            $movimiento->recibos_id = $recibo->id;
            $movimiento->terceros_id = $cliente->terceros_id;
            $movimiento->save();
        }

        $facxrec->save();
        if ($descuento > 0) {
            $ctdescuento = Cuenta::find(159);
            $movimiento = new Movimiento();
            $movimiento->fecha = $fecha;
            $movimiento->valor = $descuento;
            $movimiento->naturaleza = "Débito";
            $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . ' Descuento pago completo';
            $movimiento->cuentas_id = $ctdescuento->id;
            $movimiento->recibos_id = $recibo->id;
            $movimiento->terceros_id = $cliente->terceros_id;
            $movimiento->save();
        }

        $movimiento = new Movimiento();
        $movimiento->fecha = $fecha;
        $movimiento->valor = $pagado;
        $movimiento->naturaleza = "Débito";
        $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $request->input('observaciones') . '.';
        $movimiento->cuentas_id = $forma->cuenta->id;
        $movimiento->recibos_id = $recibo->id;
        $movimiento->terceros_id = $cliente->terceros_id;
        $movimiento->save();

        $credito->estado = "Finalizado";
        $credito->save();

        return redirect('/creditos/' . $credito->id . '/plan_pagos')->with('pago', $pago->id);
    }

    public function descargarRecibo($pago)
    {
        $fecha = Carbon::now();
        $pago = Pago::with(['credito' => function ($q) {
            $q->with('cliente', 'cuotas');
        }, 'recibo' => function ($q) {
            $q->with('facturas', 'movimientos');
        }, 'formaPago.cuenta'])->find($pago);
        $formater = new NumeroALetras();
        $letras = $formater->toWords($pago->valor, 2);
        $ultimo = FacturasxRecibo::where('recibos_id', $pago->recibo->id)->latest('id')->first();
        if ($ultimo->saldo == null) {
            $deuda = 0;
            foreach ($pago->credito->cuotas as $cuota) {
                if ($cuota->estado != "Pagada") {
                    $deuda = $deuda + $cuota->saldo_capital + $cuota->saldo_interes + $cuota->saldo_mora;
                }
            }
            $ultimo->saldo = $deuda;
            $ultimo->save();
        }

        $dompdf = PDF::loadView('pagos.reciboCaja', compact('pago', 'fecha', 'letras', 'ultimo'));
        return $dompdf->stream("Recibo.pdf");
    }

    public function pagosPorCredito($credito)
    {
        $credito = Credito::with(['pagos' => function ($q) {
            $q->with('recibo', 'cuotas');
        }, 'cliente'])->find($credito);

        return view('pagos.cuotas', compact('credito'));
    }

    public function pagarCuotasView(Request $request)
    {
        $idcuotas = $request->input('idcuotas');
        $cli = $request->input('cliente');
        $cliente = User::with('tercero')->whereHas('tercero', function ($q) use ($cli) {
            $q->where('id', $cli);
        })->first();
        $credito = Credito::with(["cuotas" => function ($q) use ($idcuotas) {
            $q->whereIn('id', $idcuotas)->where('estado', '!=', 'Pagada');
        }])->find($request->input('credito'));
        $formas = FormaPago::get();

        return view('pagos.pagarCuotas', compact('cliente', 'credito', 'formas'));
    }

    public function pagarCuotas(Request $request)
    {
        try {
            $listaCuotas = json_decode($request->input('cuotas'));
            $cliente = User::with('tercero')->find($request->input('cliente'));
            $credito = Credito::with('factura', 'cuotas', 'tipos')->find($request->input('credito'));
            $forma = FormaPago::find($request->input('forma'));
            $fecha = Carbon::now();
            $ajuste = $request->input('ajuste');
            $valorPagado = $request->input('pago');

            $recibo = new Recibo();
            $ultimo = Recibo::where('prefijo', 'RC1')->where('year', $fecha->year)->where('fecha', '>=', '2023-01-05')->orderBy('numero', 'desc')->first();
            if ($ultimo != null) {
                $recibo->numero = $ultimo->numero + 1;
            } else {
                $recibo->numero = 1;
            }
            $recibo->prefijo = "RC1";
            $recibo->fecha = $fecha;
            $recibo->year = $fecha->year;
            $recibo->valor = $valorPagado;
            $recibo->save();

            $facxrec = new FacturasxRecibo();
            $facxrec->facturas_id = $credito->factura->id;
            $facxrec->recibos_id = $recibo->id;
            $facxrec->abono = $recibo->valor;

            $pago = new Pago();
            $pago->fecha = $fecha;
            $pago->valor = $valorPagado;
            $pago->users_id = $cliente->id;
            $pago->observaciones = $recibo->prefijo . " " . $recibo->numero . " " . $request->input('observaciones') . '.';
            $pago->creditos_id = $credito->id;
            $pago->formas_pago_id = $forma->id;
            $pago->recibos_id = $recibo->id;
            $pago->save();

            $tmora = 0;
            $tinteres = 0;
            $tcapital = 0;
            foreach ($listaCuotas as $objetoCuota) {
                $mora = 0;
                $interes = 0;
                $capital = 0;
                $cuota = Cuota::find(explode("-", $objetoCuota->ide)[1]);
                $valor = $objetoCuota->valor;
                if ($cuota->saldo_mora > 0) {
                    if ($valor >= $cuota->saldo_mora || abs($valor - $cuota->saldo_mora) < 0.00001) {
                        $mora = $mora + $cuota->saldo_mora;
                        $valor = $valor - $cuota->saldo_mora;
                        $cuota->saldo_mora = 0;
                        $cuota->fecha_mora = $fecha;
                    } else {
                        $mora = $mora + $valor;
                        $cuota->saldo_mora = $cuota->saldo_mora - $valor;
                        $valor = 0;
                    }
                    $cuota->interes_mora = $cuota->interes_mora + $mora;
                }
                if ($cuota->saldo_interes > 0 && $valor > 0) {
                    if (abs($valor - $cuota->saldo_interes) < 0.00001  || $valor > $cuota->saldo_interes) {
                        $interes = $interes + $cuota->saldo_interes;
                        $valor = $valor - $cuota->saldo_interes;
                        $cuota->saldo_interes = 0;
                    } else {
                        $interes = $interes + $valor;
                        $cuota->saldo_interes = $cuota->saldo_interes - $valor;
                        $valor = 0;
                    }
                }
                if ($cuota->saldo_capital > 0 && $valor > 0) {
                    if (abs($valor - $cuota->saldo_capital) < 0.00001) {
                        $capital = $capital + $cuota->saldo_capital;
                        $valor = $valor - $cuota->saldo_capital;
                        $cuota->saldo_capital = 0;
                        $ultimapaga = $cuota->ncuota;
                        $cuota->estado = "Pagada";
                    } else {
                        $capital = $capital + $valor;
                        $cuota->saldo_capital = $cuota->saldo_capital - $valor;
                        $valor = 0;
                    }
                }
                $cuota->save();

                $pagoCuota = new Pagos_Cuotas();
                $pagoCuota->pagos_id = $pago->id;
                $pagoCuota->cuotas_id = $cuota->id;
                $pagoCuota->mora = $mora;
                $pagoCuota->interes = $interes;
                $pagoCuota->capital = $capital;
                $pagoCuota->save();

                $tmora = $tmora + $mora;
                $tinteres = $tinteres + $interes;
                $tcapital = $tcapital + $capital;
            }

            if (isset($ultimapaga)) {
                $credito->pagadas = $ultimapaga;
                if (count($credito->cuotas) == $ultimapaga) {
                    $credito->estado = "Finalizado";
                }
                $credito->save();
            }

            $ctmora = Cuenta::find(108);
            $ctinteres = Cuenta::find(107);
            $ctcreditos = Cuenta::find(212);
            if(count($credito->tipos) == 1){
                if($credito->tipos[0]->id == 4 && Carbon::parse($credito->fecha_prestamo)->gt(Carbon::parse("2023-04-23"))){
                    $ctcreditos = Cuenta::find(233);
                }else if($credito->tipos[0]->id > 2 && Carbon::parse($credito->fecha_prestamo)->gt(Carbon::parse("2023-05-06"))){
                    $ctcreditos = Cuenta::find(233);
                }
            }

            if ($tmora > 0) {
                $facxrec->mora = $tmora;
                $movimiento = new Movimiento();
                $movimiento->fecha = $fecha;
                $movimiento->valor = $tmora;
                $movimiento->naturaleza = "Crédito";
                $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . " Intereses de mora";
                $movimiento->cuentas_id = $ctmora->id;
                $movimiento->recibos_id = $recibo->id;
                $movimiento->terceros_id = $cliente->terceros_id;
                $movimiento->save();
            }
            if ($tinteres > 0) {
                $movimiento = new Movimiento();
                $movimiento->fecha = $fecha;
                $movimiento->valor = $tinteres;
                $movimiento->naturaleza = "Crédito";
                $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $ctinteres->nombre;
                $movimiento->cuentas_id = $ctinteres->id;
                $movimiento->recibos_id = $recibo->id;
                $movimiento->terceros_id = $cliente->terceros_id;
                $movimiento->save();

                if ($credito->antiguo == 1) {
                    $nota = new NtaContabilidad();
                    $nota->prefijo = "NCO";
                    $ultima = NtaContabilidad::where('prefijo', $nota->prefijo)->where('year', $fecha->year)->orderBy('numero', 'desc')->first();
                    if ($ultima != null) {
                        $nota->numero = $ultima->numero + 1;
                    } else {
                        $nota->numero = 1;
                    }
                    $nota->fecha = $fecha;
                    $nota->year = $fecha->year;
                    $nota->concepto = "Ajuste de interesés corrientes";
                    $nota->terceros_id = $cliente->terceros_id;
                    $nota->save();

                    $ctantaint = Cuenta::find(256);
                    $movimiento = new Movimiento();
                    $movimiento->fecha = $fecha;
                    $movimiento->valor = $tinteres;
                    $movimiento->naturaleza = "Débito";
                    $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $ctantaint->nombre;
                    $movimiento->cuentas_id = $ctantaint->id;
                    $movimiento->ntscontabilidad_id = $nota->id;
                    $movimiento->terceros_id = $cliente->terceros_id;
                    $movimiento->save();
                    $movimiento = new Movimiento();
                    $movimiento->fecha = $fecha;
                    $movimiento->valor = $tinteres;
                    $movimiento->naturaleza = "Crédito";
                    $movimiento->concepto =  $recibo->prefijo . " " . $recibo->numero . " " . $ctcreditos->nombre;
                    $movimiento->cuentas_id = $ctcreditos->id;
                    $movimiento->ntscontabilidad_id = $nota->id;
                    $movimiento->terceros_id = $cliente->terceros_id;
                    $movimiento->save();
                }
            }
            if ($tcapital > 0) {
                $movimiento = new Movimiento();
                $movimiento->fecha = $fecha;
                $movimiento->valor = $tcapital;
                $movimiento->naturaleza = "Crédito";
                $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $ctcreditos->nombre;;
                $movimiento->cuentas_id = $ctcreditos->id;
                $movimiento->recibos_id = $recibo->id;
                $movimiento->terceros_id = $cliente->terceros_id;
                $movimiento->save();
            }

            if ($ajuste != 0) {
                $movimiento = new Movimiento();
                if ($ajuste > 0) {
                    $ctaAjuste = Cuenta::find(282);
                    $movimiento->naturaleza = "Crédito";
                } else {
                    $ctaAjuste = Cuenta::find(164);
                    $movimiento->naturaleza = "Débito";
                    $ajuste = $ajuste * -1;
                }
                $movimiento->fecha = $fecha;
                $movimiento->valor = $ajuste;
                $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $ctaAjuste->nombre;;
                $movimiento->cuentas_id = $ctaAjuste->id;
                $movimiento->recibos_id = $recibo->id;
                $movimiento->terceros_id = $cliente->terceros_id;
                $movimiento->save();
            }

            $facxrec->save();
            $movimiento = new Movimiento();
            $movimiento->fecha = $fecha;
            $movimiento->valor = $valorPagado;
            $movimiento->naturaleza = "Débito";
            $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $request->input('observaciones');
            $movimiento->saldo = $forma->cuenta->total;
            $movimiento->cuentas_id = $forma->cuenta->id;
            $movimiento->recibos_id = $recibo->id;
            $movimiento->terceros_id = $cliente->terceros_id;
            $movimiento->save();

            return json_encode(["respuesta" => "success", "msj" => $pago->id]);
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . "--" . $ex->getLine()]);
        }

        //return redirect('/creditos/' . $credito->id . '/plan_pagos')->with('pago', $pago->id);
    }

    public function pagarFacturasView(Request $request)
    {
        $idfacturas = $request->input('idfacturas');
        $tercero = TerceroJADMIN::find($request->input('cliente'));
        //$cliente = User::with('tercero')->find($request->input('cliente'));
        $facturas = Factura::with('productos.contrapartida')->whereIn('id', $idfacturas)->get();
        foreach ($facturas as $factura) {
            $fc = FacturasxRecibo::where('facturas_id', $factura->id)->whereHas('recibo', function ($q) {
                $q->where('estado', 1);
            })->latest('id')->first();
            if ($fc == null) {
                $ncreds = Nota::with('movimientos')->where('prefijo', 'NC')->where('facturas_id', $factura->id)->get();
                $cobrar = 0;
                $contras = [];
                foreach ($factura->productos as $producto) {
                    if (!in_array($producto->contrapartida->id, $contras)) {
                        $movimiento = Movimiento::where('facturas_id', $factura->id)->where('cuentas_id', $producto->contrapartida->id)->first();
                        if ($movimiento != null) {
                            $snotas = 0;
                            foreach ($ncreds as $ncred) {
                                foreach ($ncred->movimientos as $ncmov) {
                                    if ($producto->contrapartida->id == $ncmov->cuentas_id) {
                                        $snotas = $snotas + $ncmov->valor;
                                        break;
                                    }
                                }
                            }
                            $cobrar = $cobrar + $movimiento->valor - $snotas;
                        } else {
                            $cobrar = $factura->saldo;
                            break;
                        }
                        $contras[] = $producto->contrapartida->id;
                    }
                }
            } else {
                $cobrar = $fc->saldo;
            }
            $factura->cobrar = $cobrar;
            $formas = FormaPago::get();
            $retefuentes = Retefuente::get();
            $reteicas = Reteica::get();
            $reteivas = Reteiva::get();
            $extras = Extra::get();
        }

        return view('pagos.pagarFacturas', compact('tercero', 'idfacturas', 'facturas', 'formas', 'retefuentes', 'reteicas','reteivas' ,'extras'));
    }
}
