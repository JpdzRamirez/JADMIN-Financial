<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\Recibo;
use App\Models\TerceroCahors;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Luecano\NumeroALetras\NumeroALetras;

class ComprobanteController extends Controller
{
    public function listaEgresos()
    {
        $fecha = Carbon::now();
        $envyear = $fecha->year;
        $egresos = Comprobante::with('tercero')->where('tipo', 'Egreso')->where('year', $fecha->year)->orderBy('id', 'desc')->paginate(10);

        return view('comprobantes.listaEgresos', compact('egresos', 'fecha', 'envyear'));
    }

    public function nuevoEgreso()
    {
        /*$formas = FormaPago::get();
        $facturas = Factura::where('tipo', 'Compra')->where('cruzada', 0)->get();*/

        return view('comprobantes.nuevoEgreso');
    }

    public function registrarEgreso(Request $request)
    {
        $fecha = Carbon::now();
        $terce = explode("_", $request->input('tercero'));
        $debitos = 0;
        $creditos = 0;
        try {
            DB::beginTransaction();
            $ultimo = Comprobante::where('prefijo', $request->input('prefijo'))->where('year', $fecha->year)->orderBy('id', 'desc')->first();
            $comprobante = new Comprobante();
            if ($ultimo != null) {
                $comprobante->numero = $ultimo->numero + 1;
            } else {
                $comprobante->numero = 1;
            }
            $comprobante->prefijo = $request->input('prefijo');
            $comprobante->tipo = "Egreso";
            $comprobante->valor = $request->input('total');
            $comprobante->concepto = $request->input('concepto');
            $comprobante->fecha = $fecha;
            $comprobante->year = $fecha->year;
            $comprobante->terceros_id = $terce[0];
            $comprobante->save();

            $datos = json_decode($request->input('datos'));

            foreach ($datos as $dato) {
                $cuenta = Cuenta::find($dato->id);
                if ($cuenta->naturaleza == "Crédito") {
                    if ($dato->movi == "Crédito") {
                        $creditos = $creditos + $dato->valor;
                        $cuenta->total = $cuenta->total + $dato->valor;
                    } else {
                        $debitos = $debitos + $dato->valor;
                        $cuenta->total = $cuenta->total - $dato->valor;
                    }
                } else {
                    if ($dato->movi == "Débito") {
                        $debitos = $debitos + $dato->valor;
                        $cuenta->total = $cuenta->total + $dato->valor;
                    } else {
                        $creditos = $creditos + $dato->valor;
                        $cuenta->total = $cuenta->total - $dato->valor;
                    }
                }
                $cuenta->save();
                $termov = explode("_", $dato->tercero);
                $movimiento = new Movimiento();
                $movimiento->naturaleza = $dato->movi;
                $movimiento->fecha = $fecha;
                $movimiento->valor = $dato->valor;
                $movimiento->concepto = $comprobante->prefijo . " " . $comprobante->numero . " " . $comprobante->concepto;
                $movimiento->saldo = $cuenta->total;
                $movimiento->cuentas_id = $cuenta->id;
                $movimiento->comprobantes_id = $comprobante->id;
                $movimiento->terceros_id = $termov[0];
                $movimiento->save();
            }

            if (abs($debitos - $creditos) <= 1) {
                DB::commit();
            }else{
                throw new Exception("El asiento no está balanceado");
            }

            return json_encode(["respuesta" => "success", "msj" => $comprobante->id]);
        } catch (Exception $ex) {
            DB::rollBack();

            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage()]);
        }
    }

    public function descargarComprobante($comprobante)
    {

        $comprobante = Comprobante::with(["tercero" => function ($q) {
            $q->with('usuario', 'empresa');
        }, "movimientos" => function ($q) {
            $q->with(["tercero" => function ($r) {
                $r->with('usuario', 'empresa');
            }, 'cuenta']);
        }])->find($comprobante);
        $fecha = Carbon::now();

        $dompdf = PDF::loadView('comprobantes.imprimir', compact('comprobante', 'fecha'));
        return $dompdf->stream($comprobante->prefijo . " " . $comprobante->numero .  ".pdf");
    }

    public function listaIngresos()
    {
        $fecha = Carbon::now();
        $envyear = $fecha->year;
        $ingresos = Comprobante::with('tercero')->where('tipo', 'Ingreso')->where('year', $fecha->year)->orderBy('id', 'desc')->paginate(10);

        return view('comprobantes.listaIngresos', compact('ingresos', 'fecha', 'envyear'));
    }

    public function nuevoIngreso()
    {
        /*$formas = FormaPago::get();
        $facturas = Factura::where('tipo', 'Compra')->where('cruzada', 0)->get();*/

        return view('comprobantes.nuevoIngreso');
    }

    public function registrarIngreso(Request $request)
    {
        $fecha = Carbon::now();
        $terce = explode("_", $request->input('tercero'));
        $debitos = 0;
        $creditos = 0;
        try {
            DB::beginTransaction();
            $ultimo = Comprobante::where('prefijo', 'RC2')->where('year', $fecha->year)->orderBy('id', 'desc')->first();
            $comprobante = new Comprobante();
            if ($ultimo != null) {
                $comprobante->numero = $ultimo->numero + 1;
            } else {
                $comprobante->numero = 1;
            }
            $comprobante->prefijo = "RC2";
            $comprobante->tipo = "Ingreso";
            $comprobante->valor = $request->input('total');
            $comprobante->concepto = $request->input('concepto');
            $comprobante->fecha = $fecha;
            $comprobante->year = $fecha->year;
            $comprobante->terceros_id = $terce[0];
            $comprobante->save();

            $datos = json_decode($request->input('datos'));

            foreach ($datos as $dato) {
                $cuenta = Cuenta::find($dato->id);
                if ($cuenta->naturaleza == "Crédito") {
                    if ($dato->movi == "Crédito") {
                        $creditos = $creditos + $dato->valor;
                        $cuenta->total = $cuenta->total + $dato->valor;
                    } else {
                        $debitos = $debitos + $dato->valor;
                        $cuenta->total = $cuenta->total - $dato->valor;
                    }
                } else {
                    if ($dato->movi == "Débito") {
                        $debitos = $debitos + $dato->valor;
                        $cuenta->total = $cuenta->total + $dato->valor;
                    } else {
                        $creditos = $creditos + $dato->valor;
                        $cuenta->total = $cuenta->total - $dato->valor;
                    }
                }
                $cuenta->save();
                $movimiento = new Movimiento();
                $movimiento->naturaleza = $dato->movi;
                $movimiento->fecha = $fecha;
                $movimiento->valor = $dato->valor;
                $movimiento->concepto =  $comprobante->prefijo . " " . $comprobante->numero . " " . $comprobante->concepto;
                $movimiento->saldo = $cuenta->total;
                $movimiento->cuentas_id = $cuenta->id;
                $movimiento->comprobantes_id = $comprobante->id;
                $movimiento->terceros_id = $terce[0];
                $movimiento->save();
            }

            if (abs($debitos - $creditos) <= 1) {
                DB::commit();
            }else{
                throw new Exception("El asiento no está balanceado");
            }

            return json_encode(["respuesta" => "success", "msj" => $comprobante->id]);
        } catch (Exception $ex) {
            DB::rollBack();

            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage()]);
        }
    }

    public function imprimirAbono($recibo)
    {
        $fecha = Carbon::now();
        $recibo = Recibo::with('facturas.tercero', 'formaPago.cuenta')->find($recibo);
        $formater = new NumeroALetras();
        $letras = $formater->toWords($recibo->valor, 2);

        if ($recibo->facturas[0]->tercero->usuario != null) {
            $direccion = $recibo->facturas[0]->tercero->usuario->direccion;
            $municipio = $recibo->facturas[0]->tercero->usuario->municipio;
            $celular = $recibo->facturas[0]->tercero->usuario->celular;
        } else {
            $direccion = $recibo->facturas[0]->tercero->empresa->direccion;
            $municipio = $recibo->facturas[0]->tercero->empresa->municipio;
            $celular = $recibo->facturas[0]->tercero->empresa->telefono;
        }

        $dompdf = PDF::loadView('comprobantes.recibo', compact('recibo', 'fecha', 'letras', 'direccion', 'municipio', 'celular'));
        return $dompdf->stream("Recibo.pdf");
    }

    function anularIngreso(Request $request)
    {
        $ingreso = Comprobante::with('movimientos')->find($request->input('comprobante'));
        $ingreso->estado = "Inactivo";
        $ingreso->motivo = $request->input('motivo');
        foreach ($ingreso->movimientos as $movimiento) {
            $movimiento->estado = 0;
            $movimiento->save();
        }
        $ingreso->save();

        return redirect('/contabilidad/ingresos');
    }

    public function editarComprobante(Request $request, $comprobante)
    {
        $comprobante = Comprobante::with('movimientos', 'tercero')->find($comprobante);

        return view('comprobantes.edicion', compact('comprobante'));
    }

    public function actualizarComprobante(Request $request)
    {
        try {
            $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
            $comprobante = Comprobante::find($request->input('comprobante'));
            $comprobante->fecha = $request->input('fecha');
            $comprobante->terceros_id = $tercero->id;
            $comprobante->concepto = $request->input('concepto');
            $valor = 0;

            $editados = json_decode($request->input('editados'));
            foreach ($editados as $editado) {
                $mov = Movimiento::find($editado->id);
                $ter = TerceroCahors::select('id', 'documento')->where('documento', $editado->tercero)->first();
                $cta = Cuenta::select('id', 'codigo', 'nombre')->where('codigo', $editado->cuenta)->first();
                $valor = $valor + $editado->valor;

                $mov->terceros_id = $ter->id;
                $mov->cuentas_id = $cta->id;
                $mov->concepto = $comprobante->prefijo . " " . $comprobante->numero . " " . $cta->nombre;
                $mov->fecha = $request->input('fecha');
                $mov->valor = $editado->valor;
                $mov->naturaleza = $editado->tipo;
                $mov->save();
            }

            $nuevos = json_decode($request->input('nuevos'));
            foreach ($nuevos as $nuevo) {
                $mov = new Movimiento();
                $ter = TerceroCahors::select('id', 'documento')->where('documento', $nuevo->tercero)->first();
                $cta = Cuenta::select('id', 'codigo', 'nombre')->where('codigo', $nuevo->cuenta)->first();
                $valor = $valor + $nuevo->valor;

                $mov->terceros_id = $ter->id;
                $mov->cuentas_id = $cta->id;
                $mov->concepto = $comprobante->prefijo . " " . $comprobante->numero . " " . $cta->nombre;
                $mov->fecha = $request->input('fecha');
                $mov->valor = $nuevo->valor;
                $mov->naturaleza = $nuevo->tipo;
                $mov->comprobantes_id = $comprobante->id;
                $mov->save();
            }
            $borrados = $request->input('borrados');
            if ($borrados != null) {
                if (count($borrados) > 0) {
                    Movimiento::whereIn('id', $borrados)->delete();
                }
            }
            $comprobante->valor = $valor / 2;
            $comprobante->save();

            return json_encode(["respuesta" => "success", "msj" => $comprobante->id]);
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . "---" . $ex->getLine()]);
        }
    }

    function anularEgreso(Request $request)
    {
        $egreso = Comprobante::with('movimientos')->find($request->input('comprobante'));
        $egreso->estado = "Inactivo";
        $egreso->motivo = $request->input('motivo');
        foreach ($egreso->movimientos as $movimiento) {
            $movimiento->cuenta->save();
            $movimiento->estado = 0;
            $movimiento->save();
        }
        $egreso->save();

        return redirect('/contabilidad/egresos');
    }
}
