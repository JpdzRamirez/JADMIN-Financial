<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Costo;
use App\Models\Credito;
use App\Models\Credito_Costos;
use App\Models\Cuenta;
use App\Models\Cuota;
use App\Models\Factura;
use App\Models\Factura_detalles;
use App\Models\Movimiento;
use App\Models\Nota;
use App\Models\NtaContabilidad;
use App\Models\Placa;
use App\Models\Recibo;
use App\Models\Tasa;
use App\Models\Tercero;
use App\Models\TerceroCahors;
use App\Models\Tipocredito;
use App\Models\User;
use App\Models\Vehiculo;
use Carbon\Carbon;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Stenfrank\UBL21dian\XAdES\SignInvoice;

class TasaController extends Controller
{
    public function listarInteres()
    {
        $tasas = Tasa::where('tipo', 'Interés')->orderBy('id', 'desc')->paginate(15);

        return view('tasas.interes', compact('tasas'));
    }

    public function listarMora()
    {
        $tasas = Tasa::where('tipo', 'Mora')->orderBy('id', 'desc')->paginate(15);

        return view('tasas.mora', compact('tasas'));
    }

    public function addTasaInteres(Request $request)
    {
        $tasa = Tasa::where('year', $request->input('year'))->where('mes', $request->input('mes'))->where('tipo', 'Interés')->first();
        if ($tasa == null) {
            $tasa = new Tasa();
            $tasa->tipo = "Interés";
            $tasa->year = $request->input('year');
            $tasa->mes = $request->input('mes');
            $tasa->valor = $request->input('valor');
            $tasa->save();

            return redirect('/tasas_interes');
        } else {
            return back()->withErrors(["sql" => "La tasa para el periodo ingresado ya está registrada"]);
        }
    }

    public function addTasaMora(Request $request)
    {
        $tasa = Tasa::where('year', $request->input('year'))->where('mes', $request->input('mes'))->where('tipo', 'Mora')->first();
        if ($tasa == null) {
            $tasa = new Tasa();
            $tasa->tipo = "Mora";
            $tasa->year = $request->input('year');
            $tasa->mes = $request->input('mes');
            $tasa->valor = $request->input('valor');
            $tasa->save();

            return redirect('/tasas_mora');
        } else {
            return back()->withErrors(["sql" => "La tasa para el periodo ingresado ya está registrada"]);
        }
    }

    public function placasPorCliente(Request $request, $cliente)
    {
        $cliente = User::find($cliente);
        $json = file_get_contents("https://crm.apptaxcenter.com/integracion/placas_propietario?key=97215612&identificacion=" . $cliente->nro_identificacion);
        $cliente->placas = json_decode($json);

        if ($request->ajax()) {
            return $json;
        } else {
            return view('vehiculos.lista', compact('cliente'));
        }
    }

    public function importarCreditos()
    {
        set_time_limit(0);
        $objPHPExcel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR .  "credito.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $numRows = $sheet->getHighestRow();

        try {
            if ($numRows > 0) {
                for ($i = 1; $i <= $numRows; $i++) {
                    $user = User::where('nro_identificacion', $sheet->getCell('A' . $i)->getCalculatedValue())->first();
                    if ($user == null) {
                        $terceroIcon = Tercero::where('NRO_IDENTIFICACION', $sheet->getCell('A' . $i))->first();
                        $tercero = new TerceroCahors();
                        $tercero->tipo = "Persona";
                        $tercero->nombre = utf8_encode($terceroIcon->RAZON_SOCIAL);
                        $tercero->documento =  $sheet->getCell('A' . $i);
                        $tercero->save();

                        $user = new User();
                        $user->nro_identificacion =  $sheet->getCell('A' . $i);
                        $user->tipo_identificacion = "Cédula de ciudadanía";
                        $user->primer_apellido = utf8_encode($terceroIcon->PRIMER_APELLIDO);
                        $user->segundo_apellido = utf8_encode($terceroIcon->SEGUNDO_APELLIDO);
                        $user->primer_nombre = utf8_encode($terceroIcon->PRIMER_NOMBRE);
                        $user->segundo_nombre = utf8_encode($terceroIcon->SEGUNDO_NOMBRE);
                        $user->direccion = utf8_encode($terceroIcon->DIRECCION);
                        $user->barrio = utf8_encode($terceroIcon->BARRIO);
                        $user->celular = $terceroIcon->CELULAR;
                        $user->email = utf8_encode($terceroIcon->EMAIL);
                        $user->estado = 1;
                        $user->rol = 2;
                        $user->usuario = $user->nro_identificacion;
                        $user->password = Hash::make($user->nro_identificacion);
                        $user->terceros_id = $tercero->id;
                        $iden = $user->nro_identificacion;
                        $vehiculos = Vehiculo::with('empresa')->whereHas('propietario', function ($q) use ($iden) {
                            $q->whereHas('tercero', function ($r) use ($iden) {
                                $r->where('NRO_IDENTIFICACION', $iden);
                            });
                        })->orWhereHas('otrosPropietarios', function ($q) use ($iden) {
                            $q->whereHas('tercero', function ($r) use ($iden) {
                                $r->where('NRO_IDENTIFICACION', $iden);
                            });
                        })->get();
                        if (count($vehiculos) > 0) {
                            $user->condicion = "Propietario";
                        } else {
                            $user->condicion = "Particular";
                        }
                        $user->save();

                        foreach ($vehiculos as $vehiculo) {
                            $placa = new Placa();
                            $placa->placa = $vehiculo->PLACA;
                            $placa->empresa = $vehiculo->empresa->SIGLA;
                            $placa->users_id = $user->id;
                            $placa->save();
                        }
                    }

                    $mon = $sheet->getCell('H' . $i)->getCalculatedValue();
                    $monto = $mon;
                    $plc = $sheet->getCell('C' . $i)->getCalculatedValue();
                    if ($plc == "Seg Vida") {
                        $costos = Costo::whereIn('id', [5, 6, 7])->get();
                    } else {
                        $costos = Costo::whereIn('id', [1, 2])->get();
                    }
                    $numFactura = explode(" ", $sheet->getCell('E' . $i)->getCalculatedValue());
                    foreach ($costos as $costo) {
                        if ($costo->tipo == "Absoluto") {
                            if ($costo->iva == "1") {
                                $iva = $costo->valor * 1.19;
                                $monto = $monto + $iva;
                            } else {
                                $monto = $monto + $costo->valor;
                            }
                        } else {
                            if ($costo->iva == "1") {
                                $iva = (($mon * $costo->valor) / 100) * 0.19;
                                $monto = $monto + $iva;
                            } else {
                                $monto = $monto +  (($mon * $costo->valor) / 100);
                            }
                        }
                    }

                    $plazo =  $sheet->getCell('I' . $i)->getCalculatedValue();
                    $tasa = $sheet->getCell('J' . $i)->getCalculatedValue();
                    if ($tasa == 0) {
                        $cuota = $monto / $plazo;
                    } else {
                        $tasamv = pow(1 + $tasa, 1 / 12) - 1;
                        $cuota = $monto * (($tasamv * pow(1 + $tasamv, $plazo)) / (pow(1 + $tasamv, $plazo) - 1));
                    }

                    $credito = new Credito();
                    $credito->monto = $mon;
                    $credito->monto_total = $monto;
                    $credito->pagadas = 0;
                    $credito->plazo = $plazo;
                    $credito->tipo = "Seguro de Vida";
                    $credito->tasa = $tasa * 100;
                    $credito->fecha = $sheet->getCell('B' . $i)->getValue();
                    $credito->pago = $cuota;
                    $credito->estado = "En cobro";
                    $credito->users_id = $user->id;

                    $placa = Placa::where('placa', $sheet->getCell('C' . $i)->getCalculatedValue())->first();
                    if ($placa != null) {
                        $credito->placas_id = $placa->id;
                    }
                    $fechaTexto = $sheet->getCell('M' . $i)->getCalculatedValue();
                    $hoy = Carbon::parse($fechaTexto);
                    $credito->fecha_prestamo = $hoy;
                    $credito->save();

                    foreach ($costos as $costo) {
                        $creditoCosto = new Credito_Costos();
                        $creditoCosto->creditos_id = $credito->id;
                        $creditoCosto->costos_id = $costo->id;
                        if ($costo->tipo == "Absoluto") {
                            $creditoCosto->valor = $costo->valor;
                        } else {
                            $creditoCosto->valor = ($mon * $costo->valor) / 100;
                        }
                        $creditoCosto->save();
                    }

                    $credito = Credito::with(['costos' => function ($q) {
                        $q->with('cuenta');
                    }])->find($credito->id);
                    $saldo = $credito->monto_total;
                    $mv = pow(1 + ($credito->tasa / 100), 30 / 360) - 1;
                    for ($j = 1; $j <= $credito->plazo; $j++) {
                        $cuota = new Cuota();
                        $cuota->ncuota = $j;
                        $cuota->saldo_insoluto = $saldo;
                        $cuota->interes = $mv * $saldo;
                        $cuota->abono_capital = $credito->pago - $cuota->interes;
                        $cuota->fecha_vencimiento = $hoy->addMonth();
                        if ($j == 1) {
                            $cuota->estado = "Vigente";
                        } else {
                            $cuota->estado = "Pendiente";
                        }
                        $cuota->saldo_capital = $cuota->abono_capital;
                        $cuota->saldo_interes = $cuota->interes;
                        $cuota->saldo_mora = 0;
                        $cuota->fecha_mora = $cuota->fecha_vencimiento;
                        $cuota->creditos_id = $credito->id;
                        $cuota->mora = 0;
                        $cuota->save();

                        $saldo = $saldo - $cuota->abono_capital;
                    }

                    $factura = new Factura();
                    $factura->prefijo = $numFactura[0];
                    $factura->numero = $numFactura[1];
                    $factura->descripcion = "Factura de venta #" . $factura->prefijo . $factura->numero;
                    $factura->fecha = $fechaTexto;
                    $factura->valor = $credito->monto_total;
                    $factura->formapago = "Crédito";
                    $factura->tipo = "Venta";
                    $factura->creditos_id = $credito->id;
                    $factura->terceros_id = $user->terceros_id;
                    $factura->save();
                }
            }

            return "Listo";
        } catch (Exception $ex) {
            return $ex->getMessage() . "---Indice:" . $i;
        }
    }

    public function importarNotas(Request $request)
    {
        set_time_limit(0);
        $excel = IOFactory::load($request->file('filenota'));
        $hoja = $excel->setActiveSheetIndex(1);
        $numRows = $hoja->getHighestRow();

        DB::beginTransaction();
        try {
            if ($numRows > 1) {
                $ahora = Carbon::now();
                $nota = new NtaContabilidad();
                $ultima = NtaContabilidad::where('prefijo', 'NCO')->where('year', $ahora->year)->latest('numero')->first();
                //$ultima = NtaContabilidad::where('prefijo', 'NCA')->latest('numero')->first();
                if ($ultima != null) {
                    $nota->numero = $ultima->numero + 1;
                } else {
                    $nota->numero = 1;
                }
                $nota->fecha = $ahora;
                $nota->prefijo = "NCO";
                $nota->year = $ahora->year;
                //$nota->prefijo = "NCA";
                $nota->concepto = $request->input('concepto');
                $primer = TerceroCahors::where('documento', explode(" - ", $hoja->getCell('B2')->getValue())[0])->first();
                //$primer = TerceroCahors::where('documento', $hoja->getCell('B1')->getValue())->first();
                $nota->terceros_id = $primer->id;
                $nota->save();

                $codi = $hoja->getCell('A1')->getValue();
                $cuenta = Cuenta::where('codigo', $codi)->first();

                for ($i = 1; $i <= $numRows; $i++) {
                    $tercero = TerceroCahors::where('documento', explode(" - ", $hoja->getCell('B' . $i)->getValue())[0])->first();
                    //$tercero = TerceroCahors::where('documento', $hoja->getCell('B' . $i)->getValue())->first();
                    $valordeb = $hoja->getCell('C' . $i)->getCalculatedValue();
                    $valorcred = $hoja->getCell('D' . $i)->getCalculatedValue();
                    if ($hoja->getCell('A' . $i)->getValue() != $codi) {
                        $codi = $hoja->getCell('A' . $i)->getValue();
                        $cuenta = Cuenta::where('codigo', $codi)->first();
                    }

                    if ($tercero != null) {
                        $movimiento = new Movimiento();
                        if ($valordeb == 0) {
                            $movimiento->naturaleza = "Crédito";
                            $movimiento->valor = $valorcred;
                        } else {
                            $movimiento->naturaleza = "Débito";
                            $movimiento->valor = $valordeb;
                        }
                        $movimiento->fecha = $ahora;
                        $movimiento->concepto =$nota->prefijo . " " . $nota->numero . " " . $cuenta->nombre;
                        $movimiento->cuentas_id = $cuenta->id;
                        $movimiento->ntscontabilidad_id = $nota->id;
                        $movimiento->terceros_id = $tercero->id;
                        $movimiento->save();
                    }
                }
                DB::commit();
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return $ex->getMessage() . "---Indice:" . $ex->getLine();
        }

        return redirect('/contabilidad/notas_contables');
    }

    public function importarCompras()
    {
        DB::beginTransaction();
        set_time_limit(0);
        try {
            $hoy = Carbon::now();
            $excel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR .  "compra.xlsx");
            $hoja = $excel->setActiveSheetIndex(0);
            $numRows = $hoja->getHighestRow();

            $ultima = Factura::where('tipo', 'Compra')->where('prefijo', 'FC')->where('year', $hoy->year)->orderBy('numero', 'desc')->first();

            $tercero = TerceroCahors::where('documento', '860022137')->first();
            $factura = new Factura();
            $factura->fecha = Carbon::now();
            $factura->prefijo = "FC";
            $factura->descripcion = "NRO DE FACTURA AS443 POLIZA VG FACTURACION POLIZA 10057 VG DEL PERIODO DE 15-12-2023 AL 15-03-2024 CON UN TOTAL DE 40 ASEGURADOS";
            $factura->valor = 1274201;
            $factura->tipo = "Compra";
            $factura->numero = $ultima->numero + 1;
            $factura->terceros_id = $tercero->id;
            $factura->save();

            for ($i = 1; $i <= $numRows; $i++) {
                $detalle = new Factura_detalles();
                $detalle->cantidad = 1;
                $valorDeb = $hoja->getCell('C' . $i)->getValue();
                $valorCre = $hoja->getCell('D' . $i)->getValue();
                $detalle->valor = $valorDeb + $valorCre;
                $detalle->productos_id = 25;
                $detalle->facturas_id = $factura->id;
                $detalle->save();
                $cuenta = Cuenta::where('codigo', $hoja->getCell('A' . $i)->getValue())->first();
                $movter = TerceroCahors::where('documento', explode("-", $hoja->getCell('B'.$i)->getValue())[0])->first();
                //$movter = TerceroCahors::where('documento', $hoja->getCell('B' . $i)->getValue())->first();
                if ($movter != null) {
                    $movimiento = new Movimiento();
                    if ($valorDeb > 0) {
                        $movimiento->naturaleza = "Débito";
                    } else {
                        $movimiento->naturaleza = "Crédito";
                    }
                    $movimiento->fecha = $factura->fecha;
                    $movimiento->valor = $valorDeb + $valorCre;
                    $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .  $cuenta->nombre;
                    $movimiento->cuentas_id = $cuenta->id;
                    $movimiento->facturas_id = $factura->id;
                    $movimiento->terceros_id = $movter->id;
                    $movimiento->save();
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
        }

        return "Listo";
    }

    public function tiposCredito()
    {
        $tipos = Tipocredito::get();

        return json_encode($tipos);
    }

    public function firmarXml()
    {
        $storePath = storage_path();
        $pathCertificate = $storePath . "/claves/Certificado.pfx";
        $passwors = '3ZDVyH24R3';
        $domDocument = new DOMDocument();
        $domDocument->load($storePath . "/docs/FECR972.xml");
        $xmlView = file_get_contents($storePath . "/docs/FECR972.xml");
        $signInvoice = new SignInvoice($pathCertificate, $passwors, $xmlView);
        file_put_contents($storePath . "/FECR972.xml", $signInvoice->xml);

        return "Listo";
    }

    public function encontrarDescuadres()
    {
        $logFile = fopen("../storage/asientos.txt", 'a') or die("Error creando archivo");
        $facturas = Factura::with('movimientos')->where('fecha', '>', '2022-01-01')->get();
        foreach ($facturas as $factura) {
            $credito = 0;
            $debito = 0;
            foreach ($factura->movimientos as $movimiento) {
                if($movimiento->naturaleza == "Crédito"){
                    $credito = $credito + $movimiento->valor;
                }else{
                    $debito = $debito + $movimiento->valor;
                }
            }

            $diferencia = abs($debito - $credito);
            if( $diferencia > 1){
                fwrite($logFile, "\n" . $factura->prefijo . " " . $factura->numero . " ---- Diferencia: " . $diferencia . " ----- Crédito: " . $credito . " ------ Débito: " . $debito. " ------ Fecha: " . $factura->fecha);
            }
        }

        $notas = Nota::with('movimientos')->where('fecha', '>', '2022-01-01')->get();
        foreach ($notas as $nota) {
            $credito = 0;
            $debito = 0;
            foreach ($nota->movimientos as $movimiento) {
                if($movimiento->naturaleza == "Crédito"){
                    $credito = $credito + $movimiento->valor;
                }else{
                    $debito = $debito + $movimiento->valor;
                }
            }

            $diferencia = abs($debito - $credito);
            if( $diferencia > 1){
                fwrite($logFile, "\n" . $nota->prefijo . " " . $nota->numero . " ---- Diferencia: " . $diferencia . " ----- Crédito: " . $credito . " ------ Débito: " . $debito. " ------ Fecha: " . $nota->fecha);
            }
        }

        $recibos = Recibo::with('movimientos')->where('fecha', '>', '2022-01-01')->get();
        foreach ($recibos as $recibo) {
            $credito = 0;
            $debito = 0;
            foreach ($recibo->movimientos as $movimiento) {
                if($movimiento->naturaleza == "Crédito"){
                    $credito = $credito + $movimiento->valor;
                }else{
                    $debito = $debito + $movimiento->valor;
                }
            }

            $diferencia = abs($debito - $credito);
            if( $diferencia > 1){
                fwrite($logFile, "\n" . $recibo->prefijo . " " .  $recibo->numero . " ---- Diferencia: " . $diferencia . " ----- Crédito: " . $credito . " ------ Débito: " . $debito. " ------ Fecha: " . $recibo->fecha);
            }
        }

        $notas = NtaContabilidad::with('movimientos')->where('fecha', '>', '2022-01-01')->get();
        foreach ($notas as $nota) {
            $credito = 0;
            $debito = 0;
            foreach ($nota->movimientos as $movimiento) {
                if($movimiento->naturaleza == "Crédito"){
                    $credito = $credito + $movimiento->valor;
                }else{
                    $debito = $debito + $movimiento->valor;
                }
            }

            $diferencia = abs($debito - $credito);
            if( $diferencia > 1){
                fwrite($logFile, "\n" . $nota->prefijo . " " . $nota->numero . " ---- Diferencia: " . $diferencia . " ----- Crédito: " . $credito . " ------ Débito: " . $debito. " ------ Fecha: " . $nota->fecha);
            }
        }

        $comprobantes = Comprobante::with('movimientos')->where('fecha', '>', '2022-01-01')->get();
        foreach ($comprobantes as $comprobante) {
            $credito = 0;
            $debito = 0;
            foreach ($comprobante->movimientos as $movimiento) {
                if($movimiento->naturaleza == "Crédito"){
                    $credito = $credito + $movimiento->valor;
                }else{
                    $debito = $debito + $movimiento->valor;
                }
            }

            $diferencia = abs($debito - $credito);
            if( $diferencia > 1){
                fwrite($logFile, "\n" . $comprobante->prefijo . " " . $comprobante->numero . " ---- Diferencia: " . $diferencia . " ----- Crédito: " . $credito . " ------ Débito: " . $debito. " ------ Fecha: " . $comprobante->fecha);
            }
        }

        fclose($logFile);

        return "Listo";
    }
}
