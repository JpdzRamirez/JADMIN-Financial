<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Cuenta;
use App\Models\Cuota;
use App\Models\Factura;
use App\Models\Movimiento;
use App\Models\Nota;
use App\Models\NtaContabilidad;
use App\Models\Producto;
use App\Models\TerceroJADMIN;
use Carbon\Carbon;
use DOMDocument;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stenfrank\SoapDIAN\SOAPDIAN21;
use Stenfrank\UBL21dian\XAdES\SignCreditNote;
use Stenfrank\UBL21dian\XAdES\SignDebitNote;
use ZipArchive;

class NotaController extends Controller
{

    public function NotasCredito()
    {
        $notasCredito = Nota::with('factura')->where('tipo', 'Crédito')->orderBy('id', 'desc')->paginate(15);

        return view('notas.listaCredito', compact('notasCredito'));
    }

    public function NotasDebito()
    {
        $notasDebito = Nota::with('factura')->where('tipo', 'Débito')->orderBy('id', 'desc')->paginate(15);

        return view('notas.listaDebito', compact('notasDebito'));
    }

    public function nuevaNotaCredito($factura)
    {
        $factura = Factura::with('movimientos.cuenta', 'productos', 'credito.costos')->find($factura);
        if (count($factura->movimientos) > 0) {
            if ($factura->tipo == "Venta") {
                return view('notas.formNotaCredito2', compact('factura'));
            } else {
                return view('notas.formNotaCredito', compact('factura'));
            }
        } else {
            return view('notas.formNCAntiguos', compact('factura'));
        }
    }

    public function nuevaNotaDebito($factura)
    {
        //$factura = Factura::with('movimientos.cuenta')->find($factura);
        $factura = Factura::with('movimientos.cuenta', 'productos', 'credito.costos')->find($factura);
        if ($factura->tipo == "Venta") {
            return view('notas.formNotaDebito2', compact('factura'));
        } else {
            return view('notas.formNotaDebito', compact('factura'));
        }
    }

    public function generarNotaCreditoAntigua(Request $request)
    {
        try {
            $hoy = Carbon::now();
            $factura = Factura::with('tercero', 'credito.cliente')->find($request->input('factura'));
            $notault = Nota::where('tipo', 'Crédito')->orderBy('id', 'desc')->first();
            $notaCredito = new Nota();
            //$notaCredito = Nota::find(2);
            $notaCredito->tipo = "Crédito";
            $notaCredito->prefijo = "NC";
            $notaCredito->concepto = $request->input('concepto');
            if ($notault != null) {
                $notaCredito->numero = $notault->numero + 1;
            } else {
                $notaCredito->numero = 2;
            }
            $notaCredito->fecha = $hoy;
            $notaCredito->facturas_id = $factura->id;

            $concatnot = $notaCredito->prefijo . $notaCredito->numero;
            $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatnot);

            $desinteres = $request->input('28050510');
            $desprestamo = $request->input('28151005');
            $descentrales = $request->input('415555');
            $dessoporte = $request->input('415550');
            $desplataforma = $request->input('415535');

            $excluido = $desprestamo + $desinteres;
            $baseiva = $descentrales + $dessoporte + $desplataforma;
            $iva = $descentrales * 0.19 + $dessoporte * 0.19 + $desplataforma * 0.19;

            $cude = hash("sha384", $concatnot . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' .
                number_format($excluido + $baseiva, 2, ".", "") . '01' .
                number_format($iva, 2, ".", "") . '040.00030.00' . number_format($excluido + $baseiva + $iva, 2, ".", "") . '901318591' .
                $factura->tercero->documento . '123451');
            $qrcode = 'NroNota=' . $concatnot . PHP_EOL .
                'NitFacturador=901318591' . PHP_EOL .
                'NitAdquiriente=' . $factura->tercero->documento . PHP_EOL .
                'FechaFactura=' . $hoy->format("Y-m-d") . PHP_EOL .
                'ValorTotalFactura=' . ($excluido + $baseiva + $iva) . PHP_EOL .
                'CUDE=' . $cude . PHP_EOL .
                'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cude;
            $vencimiento = Carbon::now()->addMonth()->format('Y-m-d');
            $xmlView = view('notas.ublAntiguos', compact('factura', 'notaCredito', 'hoy', 'vencimiento', 'cude', 'segCode', 'qrcode', 'excluido', 'desinteres', 'desprestamo', 'dessoporte', 'desplataforma', 'descentrales', 'baseiva', 'iva'))->render();

            $storePath = storage_path();
            if (!is_dir($storePath . "/NC/" . $concatnot)) {
                mkdir($storePath . "/NC/" . $concatnot);
            }
            $carpeta = $storePath . "/NC/" . $concatnot . "/";
            $xmlView = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $xmlView;
            $pathCertificate = $storePath . "/claves/Certificado.pfx";
            $passwors = '3ZDVyH24R3';
            $domDocument = new DOMDocument();
            $domDocument->loadXML($xmlView);
            $singNC = new SignCreditNote($pathCertificate, $passwors, $xmlView);

            file_put_contents($storePath . "/NC/" . $concatnot . "/" . $concatnot . ".xml", $singNC->xml);
            $zip = new ZipArchive();
            $zip->open($carpeta . $concatnot . ".zip", ZipArchive::CREATE);
            $zip->addFile($carpeta . $concatnot . ".xml", $concatnot . ".xml");
            $zip->close();

            $numfact = $concatnot . ".zip";
            $contenido = base64_encode(file_get_contents($carpeta . $concatnot . ".zip"));
            $xmlPeticion = view('facturas.facturaPeticion', compact('numfact', 'contenido'))->render();

            $doc = new DOMDocument();
            $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $xmlPeticion);
            $soapdian21 = new SOAPDIAN21($storePath . "/claves/Certificado.pfx", "3ZDVyH24R3");
            $soapdian21->Action = 'http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync';
            $soapdian21->startNodes($doc->saveXML());
            $xml = $soapdian21->soap;

            $headers = array(
                "Content-type: application/soap+xml;charset=\"utf-8\"",
                "SOAPAction: http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync",
                "Content-length: " . strlen($xml),
                "Host: vpfe.dian.gov.co",
                "Connection: Keep-Alive"
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, "https://vpfe.dian.gov.co/WcfDianCustomerServices.svc");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            curl_close($ch);
            file_put_contents($storePath . "/NC/" . $concatnot . "/" . $concatnot . "Response.xml", $response);
            $pos = strpos($response, "<b:StatusCode>");
            if (substr($response, $pos, 16) == "<b:StatusCode>00" || $notaCredito->numero == "45") {
                //if(true){
                $ingresos = $descentrales + $dessoporte + $desplataforma;
                $notaCredito->cude = $cude;
                $notaCredito->save();
                $cuentas = [
                    (object)["cuenta" => 28050510, "valor" => $request->input('28050510'), "tipo" => "Débito"],
                    (object)["cuenta" => 417502, "valor" => $ingresos, "tipo" => "Débito"],
                    (object)["cuenta" => 240801, "valor" => $request->input('240801'), "tipo" => "Débito"],
                    (object)["cuenta" => 28151005, "valor" => $request->input('28151005'), "tipo" => "Débito"],
                    (object)["cuenta" => 13050501, "valor" => $request->input('13050501'), "tipo" => "Crédito"]
                ];

                foreach ($cuentas as $cuenta) {
                    $cta = Cuenta::where("codigo", $cuenta->cuenta)->first();

                    $movimiento = new Movimiento();
                    $movimiento->fecha = $hoy;
                    $movimiento->naturaleza = $cuenta->tipo;
                    $movimiento->valor = $cuenta->valor;
                    $movimiento->cuentas_id = $cta->id;
                    $movimiento->concepto = $notaCredito->prefijo . " " . $notaCredito->numero . " " . $cta->nombre;
                    $movimiento->notas_id = $notaCredito->id;
                    $movimiento->terceros_id = $factura->terceros_id;
                    $movimiento->save();
                }
                Cuota::where('creditos_id', $factura->credito->id)->where('estado', '<>', 'Pagada')->delete();
                $factura->credito->estado = "Finalizado";
                $factura->credito->save();

                return json_encode(["respuesta" => "success", "msj" => $notaCredito->id]);
            } else {
                return json_encode(["respuesta" => "error", "msj" => "No se envió la nota"]);
            }
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage()]);
        }
    }

    public function generarNotaCredito(Request $request)
    {
        $fecha = Carbon::now();
        $factura = Factura::find($request->input('factura'));
        $notault = Nota::where('tipo', 'Crédito')->orderBy('id', 'desc')->first();
        $notaCredito = new Nota();
        $notaCredito->prefijo = "NC";
        $notaCredito->tipo = "Crédito";
        if ($notault != null) {
            $notaCredito->numero = $notault->numero + 1;
        } else {
            $notaCredito->numero = 1;
        }
        $notaCredito->fecha = $fecha;
        $notaCredito->facturas_id = $factura->id;
        $notaCredito->save();

        $datos = json_decode($request->input('cuentas'));
        foreach ($datos as $dato) {
            $mov = Movimiento::with('cuenta')->find($dato->id);
            $movimiento = new Movimiento();
            if ($factura->tipo == "Venta") {
                if ($mov->naturaleza == "Crédito") {
                    $movimiento->naturaleza = "Débito";
                } else {
                    $movimiento->naturaleza = "Crédito";
                }
            } else {
                if ($mov->naturaleza == "Crédito") {
                    $movimiento->naturaleza = "Crédito";
                } else {
                    $movimiento->naturaleza = "Débito";
                }
            }
            $movimiento->fecha = $fecha;
            $movimiento->valor = $dato->valor;
            $movimiento->concepto = $notaCredito->prefijo . " " . $notaCredito->numero . " " . $mov->cuenta->nombre;
            $movimiento->cuentas_id = $mov->cuenta->id;
            $movimiento->notas_id = $notaCredito->id;
            $movimiento->terceros_id = $factura->terceros_id;
            $movimiento->save();
        }

        return redirect('contabilidad/notas_credito');
    }

    public function generarNotaDebito(Request $request)
    {
        $fecha = Carbon::now();
        $factura = Factura::find($request->input('factura'));
        $notault = Nota::where('tipo', 'Débito')->orderBy('id', 'desc')->first();
        $notaDebito = new Nota();
        $notaDebito->tipo = "Débito";
        $notaDebito->prefijo = "ND";
        if ($notault != null) {
            $notaDebito->numero = $notault->numero + 1;
        } else {
            $notaDebito->numero = 1;
        }
        $notaDebito->fecha = $fecha;
        $notaDebito->facturas_id = $factura->id;
        $notaDebito->save();

        $datos = json_decode($request->input('cuentas'));
        foreach ($datos as $dato) {
            $mov = Movimiento::with('cuenta')->find($dato->id);
            $movimiento = new Movimiento();

            if ($mov->naturaleza == "Crédito") {
                $movimiento->naturaleza = "Débito";
            } else {
                $movimiento->naturaleza = "Crédito";
            }
            $movimiento->fecha = $fecha;
            $movimiento->valor = $dato->valor;
            $movimiento->concepto = $notaDebito->prefijo . " " . $notaDebito->numero . " " .  $mov->cuenta->nombre;
            $movimiento->cuentas_id = $mov->cuenta->id;
            $movimiento->notas_id = $notaDebito->id;
            $movimiento->terceros_id = $factura->terceros_id;
            $movimiento->save();
        }

        return redirect('contabilidad/notas_debito');
    }

    public function detallesNota($nota)
    {
        $nota = Nota::with('factura', 'movimientos.tercero')->find($nota);

        return view('notas.detallesNota', compact('nota'));
    }

    public function notasContables()
    {
        $fechaenv = Carbon::now();
        $envyear = $fechaenv->year;
        $notas = NtaContabilidad::where(function($q) use($envyear){$q->where('year', $envyear)->where('prefijo', 'NCO');})->orWhere('prefijo', 'NCA')->orderBy('id', 'desc')->paginate(15);

        return view('notas.listaContables', compact('notas', 'fechaenv', 'envyear'));
    }

    public function nuevaNotaContable()
    {
        $fecha = Carbon::now();
        return view('notas.formNotaContable', compact('fecha'));
    }

    public function editarNotaContable(Request $request, $notaContable)
    {

        $notaContable = NtaContabilidad::with('movimientos', 'tercero')->find($notaContable);
        return view('notas.edicionNotaContable', compact('notaContable'));
    }

    public function actualizarNotaContable(Request $request)
    {
        try {
            $tercero = TerceroJADMIN::where('documento', $request->input('tercero'))->first();
            $notaContable = NtaContabilidad::find($request->input('notaContable'));
            $notaContable->fecha = $request->input('fecha');
            $notaContable->terceros_id = $tercero->id;
            $notaContable->concepto = $request->input('concepto');
            $valor = 0;

            $editados = json_decode($request->input('editados'));
            foreach ($editados as $editado) {
                $movimiento = Movimiento::find($editado->id);
                $tercero = TerceroJADMIN::select('id', 'documento')->where('documento', $editado->tercero)->first();
                $cuenta = Cuenta::select('id', 'codigo', 'nombre')->where('codigo', $editado->cuenta)->first();
                $valor = $valor + $editado->valor;

                $movimiento->terceros_id = $tercero->id;
                $movimiento->cuentas_id = $cuenta->id;
                $movimiento->concepto = $notaContable->prefijo . " " . $notaContable->numero . " " . $cuenta->nombre;
                $movimiento->fecha = $request->input('fecha');
                $movimiento->valor = $editado->valor;
                $movimiento->naturaleza = $editado->tipo;
                $movimiento->save();
            }

            $nuevos = json_decode($request->input('nuevos'));
            foreach ($nuevos as $nuevo) {
                $movimiento = new Movimiento();
                $tercero = TerceroJADMIN::select('id', 'documento')->where('documento', $nuevo->tercero)->first();
                $cuenta = Cuenta::select('id', 'codigo', 'nombre')->where('codigo', $nuevo->cuenta)->first();
                $valor = $valor + $nuevo->valor;

                $movimiento->terceros_id = $tercero->id;
                $movimiento->cuentas_id = $cuenta->id;
                $movimiento->concepto = $notaContable->prefijo . " " . $notaContable->numero . " " . $cuenta->nombre;
                $movimiento->fecha = $request->input('fecha');
                $movimiento->valor = $nuevo->valor;
                $movimiento->naturaleza = $nuevo->tipo;
                $movimiento->ntscontabilidad_id = $notaContable->id;
                $movimiento->save();
            }
            $borrados = $request->input('borrados');
            if ($borrados != null) {
                if (count($borrados) > 0) {
                    Movimiento::whereIn('id', $borrados)->delete();
                }
            }
            //$notaContable->valor = $valor/2;
            $notaContable->save();

            return json_encode(["respuesta" => "success", "msj" => $notaContable->id]);
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . "---" . $ex->getLine()]);
        }
    }

    public function generarNotaContable(Request $request)
    {
        $fecha = $request->input('fecha');
        $ter = explode("_", $request->input('tercero'));
        $debitos = 0;
        $creditos = 0;
        try {
            DB::beginTransaction();
            $tercero = TerceroJADMIN::find($ter[0]);
            $nota = new NtaContabilidad();
            $nota->prefijo = $request->input('tipon');
            $fechaenv = Carbon::now();
            if ($nota->prefijo == "NCO") {
                $ultima = NtaContabilidad::where('prefijo', $nota->prefijo)->where('year', $fechaenv->year)->orderBy('numero', 'desc')->first();
            }else{
                $ultima = NtaContabilidad::where('prefijo', $nota->prefijo)->orderBy('numero', 'desc')->first();
            }
            if ($ultima != null) {
                $nota->numero = $ultima->numero + 1;
            } else {
                $nota->numero = 1;
            }
            $nota->concepto = $request->input('concepto');
            $nota->fecha = $fecha;
            $nota->year = $fechaenv->year;
            $nota->terceros_id = $tercero->id;
            $nota->save();

            $datos = json_decode($request->input('datos'));

            foreach ($datos as $dato) {
                $cuenta = Cuenta::find($dato->id);
                $termov = explode("_", $dato->tercero);

                if($dato->movi == 'Débito'){
                    $debitos = $debitos + $dato->valor;
                }else{
                    $creditos = $creditos + $dato->valor;
                }

                $movimiento = new Movimiento();
                $movimiento->naturaleza = $dato->movi;
                $movimiento->fecha = $fecha;
                $movimiento->valor = $dato->valor;
                $movimiento->concepto = $nota->prefijo . " " . $nota->numero . " " . $cuenta->nombre;
                $movimiento->cuentas_id = $cuenta->id;
                $movimiento->ntscontabilidad_id = $nota->id;
                $movimiento->terceros_id = $termov[0];
                $movimiento->save();
            }

            if (abs($debitos - $creditos) <= 1) {
                DB::commit();
            }else{
                throw new Exception("El asiento no está balanceado");
            }

            return json_encode(["respuesta" => "success", "msj" => $nota->id]);
        } catch (Exception $ex) {
            DB::rollBack();

            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage()]);
        }
    }

    public function detallesNotaContable($nota)
    {
        $nota = NtaContabilidad::with('movimientos')->find($nota);

        return view('notas.detallesNotaContable', compact('nota'));
    }

    public function descargarNota($nota)
    {
        $nota = Nota::with('factura.tercero', 'movimientos.tercero')->find($nota);
        $fecha = Carbon::now();
        $dompdf = PDF::loadView('notas.impresionNota', compact('nota', 'fecha'));

        return $dompdf->stream("Nota.pdf");
    }

    public function descargarNotaContable($nota)
    {
        ini_set('memory_limit', -1);
        $nota = NtaContabilidad::with('tercero', 'movimientos')->find($nota);
        $fecha = Carbon::now();
        $dompdf = PDF::loadView('notas.impresionNotaContable', compact('nota', 'fecha'));

        return $dompdf->stream("Nota.pdf");
    }

    public function generarNotaCreditoNueva(Request $request)
    {
        try {
            $hoy = Carbon::now();
            $factura = Factura::with('tercero', 'credito', 'productos')->find($request->input('factura'));
            $notault = Nota::where('prefijo', 'NC')->orderBy('numero', 'desc')->first();
            $notaCredito = new Nota();
            $notaCredito->tipo = "Crédito";
            $notaCredito->prefijo = "NC";
            $notaCredito->concepto = $request->input('concepto');
            if ($notault != null) {
                $notaCredito->numero = $notault->numero + 1;
            } else {
                $notaCredito->numero = 1;
            }
            $notaCredito->fecha = $hoy;
            $notaCredito->facturas_id = $factura->id;

            $concatnot = $notaCredito->prefijo . $notaCredito->numero;
            $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatnot);

            $productos = json_decode($request->input('productos'));

            $iva = 0;
            $baseiva = 0;
            $excluido = 0;
            foreach ($productos as $producto) {
                if ($request->input('tipopro') == "Costos") {
                    if ($producto->id == 0) {
                        $producto->item = Producto::with('cuenta', 'contrapartida')->find(30);
                        $producto->nombre = $producto->item->nombre;
                    } else {
                        $producto->item = Costo::with('cuenta')->find($producto->id);
                        $producto->nombre = $producto->item->descripcion;
                    }
                } else {
                    $producto->item = Producto::with('cuenta', 'contrapartida')->find($producto->id);
                    $producto->nombre = $producto->item->nombre;
                }
                if ($producto->iva == 1) {
                    $producto->valiva = $producto->valor * 0.19;
                    $iva = $iva + $producto->valiva;
                    $baseiva = $baseiva + $producto->valor;
                } else {
                    $producto->valiva = 0;
                    $excluido = $excluido + $producto->valor;
                }
            }

            $cude = hash("sha384", $concatnot . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' .
                number_format($excluido + $baseiva, 2, ".", "") . '01' .
                number_format($iva, 2, ".", "") . '040.00030.00' . number_format($excluido + $baseiva + $iva, 2, ".", "") . '901318591' .
                $factura->tercero->documento . '123451');
            $qrcode = 'NroNota=' . $concatnot . PHP_EOL .
                'NitFacturador=901318591' . PHP_EOL .
                'NitAdquiriente=' . $factura->tercero->documento . PHP_EOL .
                'FechaFactura=' . $hoy->format("Y-m-d") . PHP_EOL .
                'ValorTotalFactura=' . ($excluido + $baseiva + $iva) . PHP_EOL .
                'CUDE=' . $cude . PHP_EOL .
                'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cude;
            $vencimiento = Carbon::now()->addMonth()->format('Y-m-d');
            $motivo = $request->input('motivo');
            if ($factura->tercero->usuario != null) {
                $factura->tercero->municipio = $factura->tercero->usuario->municipio;
                $factura->tercero->direccion = $factura->tercero->usuario->direccion;
                $factura->tercero->email = $factura->tercero->usuario->email;
                $factura->tercero->celular = $factura->tercero->usuario->celular;
            } else {
                $factura->tercero->municipio = $factura->tercero->empresa->municipio;
                $factura->tercero->direccion = $factura->tercero->empresa->direccion;
                $factura->tercero->email = $factura->tercero->empresa->email;
                $factura->tercero->celular = $factura->tercero->empresa->telefono;
            }
            $xmlView = view('notas.ublNuevos', compact('factura', 'notaCredito', 'hoy', 'motivo', 'vencimiento', 'cude', 'segCode', 'qrcode', 'excluido', 'productos', 'baseiva', 'iva'))->render();

            $storePath = storage_path();
            if (!is_dir($storePath . "/NC/" . $concatnot)) {
                mkdir($storePath . "/NC/" . $concatnot);
            }
            $carpeta = $storePath . "/NC/" . $concatnot . "/";
            $xmlView = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $xmlView;
            $pathCertificate = $storePath . "/claves/Certificado.pfx";
            $passwors = '3ZDVyH24R3';
            $domDocument = new DOMDocument();
            $domDocument->loadXML($xmlView);
            $singNC = new SignCreditNote($pathCertificate, $passwors, $xmlView);

            file_put_contents($storePath . "/NC/" . $concatnot . "/" . $concatnot . ".xml", $singNC->xml);
            $zip = new ZipArchive();
            $zip->open($carpeta . $concatnot . ".zip", ZipArchive::CREATE);
            $zip->addFile($carpeta . $concatnot . ".xml", $concatnot . ".xml");
            $zip->close();

            $numfact = $concatnot . ".zip";
            $contenido = base64_encode(file_get_contents($carpeta . $concatnot . ".zip"));
            $xmlPeticion = view('facturas.facturaPeticion', compact('numfact', 'contenido'))->render();

            $doc = new DOMDocument();
            $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $xmlPeticion);
            $soapdian21 = new SOAPDIAN21($storePath . "/claves/Certificado.pfx", "3ZDVyH24R3");
            $soapdian21->Action = 'http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync';
            $soapdian21->startNodes($doc->saveXML());
            $xml = $soapdian21->soap;

            $headers = array(
                "Content-type: application/soap+xml;charset=\"utf-8\"",
                "SOAPAction: http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync",
                "Content-length: " . strlen($xml),
                "Host: vpfe.dian.gov.co",
                "Connection: Keep-Alive"
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, "https://vpfe.dian.gov.co/WcfDianCustomerServices.svc");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            curl_close($ch);
            file_put_contents($storePath . "/NC/" . $concatnot . "/" . $concatnot . "Response.xml", $response);
            $pos = strpos($response, "<b:StatusCode>");
            if (substr($response, $pos, 16) == "<b:StatusCode>00" || $notaCredito->numero == 91) {
                //if(true){
                $notaCredito->cude = $cude;
                $notaCredito->save();

                $movimientos = [];
                $ctaclientes = Cuenta::find(212);
                if ($factura->seguros_id == null) {
                    $ctadev = Cuenta::find(202);
                } else {
                    $ctadev = Cuenta::find(320);
                }
                $ctaiva = Cuenta::find(170);

                foreach ($productos as $producto) {
                    if (substr($producto->item->cuenta->codigo, 0, 1) == "4") {
                        $movimientos[] = (object)["cuenta" => $ctadev->id, "nombre" => $ctadev->nombre, "valor" => $producto->valor, "tipo" => "Débito"];
                    } else {
                        $movimientos[] = (object)["cuenta" => $producto->item->cuenta->id, "nombre" => $producto->item->cuenta->nombre, "valor" => $producto->valor, "tipo" => "Débito"];
                    }
                    if ($producto->valiva > 0) {
                        $movimientos[] = (object)["cuenta" => $ctaiva->id, "nombre" => $ctaiva->nombre, "valor" => $producto->valiva, "tipo" => "Débito"];
                    }
                    if ($request->input('tipopro') == "Costos") {
                        $movimientos[] = (object)["cuenta" => $ctaclientes->id, "nombre" => $ctaclientes->nombre, "valor" => $producto->valor + $producto->valiva, "tipo" => "Crédito"];
                    } else {
                        $movimientos[] = (object)["cuenta" => $producto->item->contrapartida->id, "nombre" => $producto->item->contrapartida->nombre, "valor" => $producto->valor + $producto->valiva, "tipo" => "Crédito"];
                    }
                }

                foreach ($movimientos as $movimiento) {
                    if (!isset($movimiento->sumado)) {
                        $total = 0;
                        foreach ($movimientos as $mov1) {
                            if ($movimiento->cuenta == $mov1->cuenta) {
                                $mov1->sumado = 1;
                                $total = $total + $mov1->valor;
                            }
                        }
                        $movi = new Movimiento();
                        $movi->fecha = $hoy;
                        $movi->naturaleza = $movimiento->tipo;
                        $movi->valor = $total;
                        $movi->cuentas_id = $movimiento->cuenta;
                        $movi->concepto = $notaCredito->prefijo . " " . $notaCredito->numero . " " . $movimiento->nombre;
                        $movi->notas_id = $notaCredito->id;
                        $movi->terceros_id = $factura->terceros_id;
                        $movi->save();
                    }
                }

                if ($motivo == '2') {
                    $factura->cruzada = 1;
                    $factura->save();

                    if ($factura->credito != null) {
                        $cuotas = Cuota::where('creditos_id', $factura->credito->id)->where('estado', '!=', 'Pagada')->get();
                        foreach ($cuotas as $cuota) {
                            $cuota->saldo_capital = 0;
                            $cuota->saldo_interes = 0;
                            $cuota->saldo_mora = 0;
                            $cuota->save();
                        }
                        $factura->credito->estado = "Finalizado";
                        $factura->credito->save();
                    }
                }

                return json_encode(["respuesta" => "success", "msj" => $notaCredito->id]);
            } else {
                return json_encode(["respuesta" => "error", "msj" => "No se envió la nota"]);
            }
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . "---" . $ex->getLine()]);
        }
    }

    public function generarNotaDebitoDIAN(Request $request)
    {
        try {
            $hoy = Carbon::now();
            $factura = Factura::with('tercero', 'credito', 'productos')->find($request->input('factura'));
            $notault = Nota::where('prefijo', 'ND')->orderBy('numero', 'desc')->first();
            $notaDebito = new Nota();
            $notaDebito->tipo = "Débito";
            $notaDebito->prefijo = "ND";
            $notaDebito->concepto = $request->input('concepto');
            if ($notault != null) {
                $notaDebito->numero = $notault->numero + 1;
            } else {
                $notaDebito->numero = 1;
            }
            $notaDebito->fecha = $hoy;
            $notaDebito->facturas_id = $factura->id;

            $concatnot = $notaDebito->prefijo . $notaDebito->numero;
            $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatnot);

            $productos = json_decode($request->input('productos'));

            $iva = 0;
            $baseiva = 0;
            $excluido = 0;
            foreach ($productos as $producto) {
                if ($request->input('tipopro') == "Costos") {
                    if ($producto->id == 0) {
                        $producto->item = Producto::with('cuenta', 'contrapartida')->find(30);
                        $producto->nombre = $producto->item->nombre;
                    } else {
                        $producto->item = Costo::with('cuenta')->find($producto->id);
                        $producto->nombre = $producto->item->descripcion;
                    }
                } else {
                    $producto->item = Producto::with('cuenta', 'contrapartida')->find($producto->id);
                    $producto->nombre = $producto->item->nombre;
                }
                if ($producto->iva == 1) {
                    $producto->valiva = $producto->valor * 0.19;
                    $iva = $iva + $producto->valiva;
                    $baseiva = $baseiva + $producto->valor;
                } else {
                    $producto->valiva = 0;
                    $excluido = $excluido + $producto->valor;
                }
            }

            $cude = hash("sha384", $concatnot . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' .
                number_format($excluido + $baseiva, 2, ".", "") . '01' .
                number_format($iva, 2, ".", "") . '040.00030.00' . number_format($excluido + $baseiva + $iva, 2, ".", "") . '901318591' .
                $factura->tercero->documento . '123451');
            $qrcode = 'NroNota=' . $concatnot . PHP_EOL .
                'NitFacturador=901318591' . PHP_EOL .
                'NitAdquiriente=' . $factura->tercero->documento . PHP_EOL .
                'FechaFactura=' . $hoy->format("Y-m-d") . PHP_EOL .
                'ValorTotalFactura=' . ($excluido + $baseiva + $iva) . PHP_EOL .
                'CUDE=' . $cude . PHP_EOL .
                'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cude;
            $vencimiento = Carbon::now()->addMonth()->format('Y-m-d');
            $motivo = $request->input('motivo');
            if ($factura->tercero->usuario != null) {
                $factura->tercero->municipio = $factura->tercero->usuario->municipio;
                $factura->tercero->direccion = $factura->tercero->usuario->direccion;
                $factura->tercero->email = $factura->tercero->usuario->email;
                $factura->tercero->celular = $factura->tercero->usuario->celular;
            } else {
                $factura->tercero->municipio = $factura->tercero->empresa->municipio;
                $factura->tercero->direccion = $factura->tercero->empresa->direccion;
                $factura->tercero->email = $factura->tercero->empresa->email;
                $factura->tercero->celular = $factura->tercero->empresa->telefono;
            }
            $xmlView = view('notas.ublDebit', compact('factura', 'notaDebito', 'hoy', 'motivo', 'vencimiento', 'cude', 'segCode', 'qrcode', 'excluido', 'productos', 'baseiva', 'iva'))->render();

            $storePath = storage_path();
            if (!is_dir($storePath . "/ND/" . $concatnot)) {
                mkdir($storePath . "/ND/" . $concatnot);
            }
            $carpeta = $storePath . "/ND/" . $concatnot . "/";
            $xmlView = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $xmlView;
            $pathCertificate = $storePath . "/claves/Certificado.pfx";
            $passwors = '3ZDVyH24R3';
            $domDocument = new DOMDocument();
            $domDocument->loadXML($xmlView);
            $singND = new SignDebitNote($pathCertificate, $passwors, $xmlView);

            file_put_contents($storePath . "/ND/" . $concatnot . "/" . $concatnot . ".xml", $singND->xml);
            $zip = new ZipArchive();
            $zip->open($carpeta . $concatnot . ".zip", ZipArchive::CREATE);
            $zip->addFile($carpeta . $concatnot . ".xml", $concatnot . ".xml");
            $zip->close();

            $numfact = $concatnot . ".zip";
            $contenido = base64_encode(file_get_contents($carpeta . $concatnot . ".zip"));
            $xmlPeticion = view('facturas.facturaPeticion', compact('numfact', 'contenido'))->render();

            $doc = new DOMDocument();
            $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $xmlPeticion);
            $soapdian21 = new SOAPDIAN21($storePath . "/claves/Certificado.pfx", "3ZDVyH24R3");
            $soapdian21->Action = 'http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync';
            $soapdian21->startNodes($doc->saveXML());
            $xml = $soapdian21->soap;

            $headers = array(
                "Content-type: application/soap+xml;charset=\"utf-8\"",
                "SOAPAction: http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync",
                "Content-length: " . strlen($xml),
                "Host: vpfe.dian.gov.co",
                "Connection: Keep-Alive"
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, "https://vpfe.dian.gov.co/WcfDianCustomerServices.svc");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            curl_close($ch);
            //$response = "prueba debito";
            file_put_contents($storePath . "/ND/" . $concatnot . "/" . $concatnot . "Response.xml", $response);
            $pos = strpos($response, "<b:StatusCode>");
            if (substr($response, $pos, 16) == "<b:StatusCode>00") {
                //if(true){
                $notaDebito->cude = $cude;
                $notaDebito->save();

                $movimientos = [];
                $ctaclientes = Cuenta::find(212);
                if ($factura->seguros_id == null) {
                    $ctadev = Cuenta::find(202);
                } else {
                    $ctadev = Cuenta::find(320);
                }
                $ctaiva = Cuenta::find(170);

                foreach ($productos as $producto) {
                    if (substr($producto->item->cuenta->codigo, 0, 1) == "4") {
                        $movimientos[] = (object)["cuenta" => $ctadev->id, "nombre" => $ctadev->nombre, "valor" => $producto->valor, "tipo" => "Crédito"];
                    } else {
                        $movimientos[] = (object)["cuenta" => $producto->item->cuenta->id, "nombre" => $producto->item->cuenta->nombre, "valor" => $producto->valor, "tipo" => "Crédito"];
                    }
                    if ($producto->valiva > 0) {
                        $movimientos[] = (object)["cuenta" => $ctaiva->id, "nombre" => $ctaiva->nombre, "valor" => $producto->valiva, "tipo" => "Crédito"];
                    }
                    if ($request->input('tipopro') == "Costos") {
                        $movimientos[] = (object)["cuenta" => $ctaclientes->id, "nombre" => $ctaclientes->nombre, "valor" => $producto->valor + $producto->valiva, "tipo" => "Débito"];
                    } else {
                        $movimientos[] = (object)["cuenta" => $producto->item->contrapartida->id, "nombre" => $producto->item->contrapartida->nombre, "valor" => $producto->valor + $producto->valiva, "tipo" => "Débito"];
                    }
                }

                foreach ($movimientos as $movimiento) {
                    if (!isset($movimiento->sumado)) {
                        $total = 0;
                        foreach ($movimientos as $mov1) {
                            if ($movimiento->cuenta == $mov1->cuenta) {
                                $mov1->sumado = 1;
                                $total = $total + $mov1->valor;
                            }
                        }
                        $movi = new Movimiento();
                        $movi->fecha = $hoy;
                        $movi->naturaleza = $movimiento->tipo;
                        $movi->valor = $total;
                        $movi->cuentas_id = $movimiento->cuenta;
                        $movi->concepto = $notaDebito->prefijo . " " . $notaDebito->numero . " " . $movimiento->nombre;
                        $movi->notas_id = $notaDebito->id;
                        $movi->terceros_id = $factura->terceros_id;
                        $movi->save();
                    }
                }

                return json_encode(["respuesta" => "success", "msj" => $notaDebito->id]);
            } else {
                return json_encode(["respuesta" => "error", "msj" => "No se envió la nota"]);
            }
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . "---" . $ex->getLine()]);
        }
    }

    public function anularNotaContable(Request $request)
    {
        $notaContable = NtaContabilidad::with('movimientos')->find($request->input('nota'));
        $notaContable->estado = "Inactivo";
        $notaContable->motivo = $request->input('motivo');

        foreach ($notaContable->movimientos as $movimiento) {
            $movimiento->estado = 0;
            $movimiento->save();
        }

        $notaContable->save();

        return redirect('/contabilidad/notas_contables');
    }
}
