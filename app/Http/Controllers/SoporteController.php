<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\Extra;
use App\Models\Factura;
use App\Models\Factura_detalles;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Resolucion;
use App\Models\Retefuente;
use App\Models\Reteica;
use App\Models\TerceroCahors;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Stenfrank\SoapDIAN\SOAPDIAN21;
use Stenfrank\UBL21dian\XAdES\SignInvoice;
use Exception;
use ZipArchive;

class SoporteController extends Controller
{
    public function nuevoSoporte()
    {
        $productos = Producto::get();
        $retefuentes = Retefuente::get();
        $reteicas = Reteica::get();
        $extras = Extra::get();
        $fecha = Carbon::now();

        return view('soportes.form', compact('productos', 'retefuentes', 'reteicas','extras', 'fecha'));
    }

    public function soportes()
    {
        $soportes = Factura::with('tercero')->where('tipo', 'Soporte')->orderBy('id', 'desc')->paginate(5);

        return view('soportes.lista', compact('soportes'));
    }

    public function guardarSoporte(Request $request)
    {
        $hoy = Carbon::now();
        try {
            $tercero = TerceroCahors::with('usuario', 'empresa')->where('documento', $request->input('tercero'))->first();
            $ultima = Factura::where('tipo', 'Soporte')->orderBy('numero', 'desc')->first();
            $soporte = new Factura();
            $soporte->descripcion = $request->input('concepto');
            $soporte->fecha = $hoy;
            $soporte->valor = $request->input('total');
            $soporte->tipo = "Soporte";
            $soporte->year = $hoy->year;
            if ($ultima != null) {
                $soporte->numero = $ultima->numero + 1;
            } else {
                $soporte->numero = "1";
            }
            $resolucion = Resolucion::where('prefijo', 'DSE')->first();
            $soporte->prefijo = $resolucion->prefijo;      
            $citec = $resolucion->citec;
            $soporte->terceros_id = $tercero->id;

            $productos = json_decode($request->input('productos'));
            $iva = 0;
            $baseiva = 0;
            $siniva = 0;
            foreach ($productos as $producto) {
                if (isset($producto->iva)) {
                    $iva = $iva + $producto->iva;
                    $baseiva = $baseiva + $producto->valor;
                } else {
                    $siniva = $siniva + $producto->valor;
                }
            }
            $concatFact = $soporte->prefijo . $soporte->numero;
            $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatFact);
            $cuds = hash("sha384", $concatFact . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' .
                number_format($soporte->valor - $iva, 2, ".", "") . '01' .
                number_format($iva, 2, ".", "") . number_format($soporte->valor, 2, ".", "") . $tercero->documento . 
                '901318591' . '12345' . '1');
            $qrcode = 'N°DocSoporte=' . $concatFact . PHP_EOL .
                'Fecha=' . $hoy->format('Y-m-d') . PHP_EOL .
                'Hora=' . $hoy->format('H:i:s') . '-05:00' . PHP_EOL .
                'ValDS=' . number_format($soporte->valor - $iva, 2, ".", "") . PHP_EOL .
                'CodImp=' . '01' . PHP_EOL .
                'ValImp=' . number_format($iva, 2, ".", "") . PHP_EOL .
                'ValTot=' . number_format($soporte->valor, 2, ".", "") . PHP_EOL .
                'NumSNO=' . $tercero->documento . PHP_EOL .
                'NITABS=' . '901318591' . PHP_EOL .
                'PIN:' . '12345' . PHP_EOL .
                'Amb:' . '1' . PHP_EOL .
                'CUDS=' . $cuds . PHP_EOL .
                'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cuds;
            $vencimiento = Carbon::parse($soporte->fecha)->addMonth();
            $finMes =  Carbon::now()->lastOfMonth();
            if ($tercero->usuario != null) {
                $tercero->municipio = $tercero->usuario->municipio;
                $tercero->direccion = $tercero->usuario->direccion;
                $tercero->email = $tercero->usuario->email;
            } else {
                $tercero->municipio = $tercero->empresa->municipio;
                $tercero->direccion = $tercero->empresa->direccion;
                $tercero->email = $tercero->empresa->email;
            }
            $xmlView = view('soportes.ublGenerica', compact('soporte', 'iva', 'siniva', 'baseiva', 'hoy', 'productos', 'tercero', 'vencimiento', 'finMes', 'cuds', 'segCode', 'qrcode', 'resolucion'))->render();

            $storePath = storage_path();
            if (!is_dir($storePath . "/facturas/" . $soporte->prefijo . "/" . $concatFact)) {
                mkdir($storePath . "/facturas/" . $soporte->prefijo . "/" . $concatFact);
            }
            $carpeta = $storePath . "/facturas/" . $soporte->prefijo . "/" . $concatFact . "/";
            $xmlView = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $xmlView;
            $pathCertificate = $storePath . "/claves/Certificado.pfx";
            $passwors = '3ZDVyH24R3';
            $domDocument = new DOMDocument();
            $domDocument->loadXML($xmlView);
            $signInvoice = new SignInvoice($pathCertificate, $passwors, $xmlView);
            Storage::disk('facturas')->put("/" . $soporte->prefijo . "/" . $concatFact . "/" . $concatFact . ".xml", $signInvoice->xml);

            $zip = new ZipArchive();
            $zip->open($carpeta . $concatFact . ".zip", ZipArchive::CREATE);
            $zip->addFile($carpeta . $concatFact . ".xml", $concatFact . ".xml");
            $zip->close();

            $numfact = $concatFact . ".zip";
            $contenido = base64_encode(file_get_contents($carpeta . $concatFact . ".zip"));
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            curl_close($ch);
            Storage::disk('facturas')->put("/" . $soporte->prefijo . "/" . $concatFact . "/" . $concatFact . "Response.xml", $response);
            $pos = strpos($response, "<b:StatusCode>");

            if (substr($response, $pos, 16) == "<b:StatusCode>00") {
                return json_encode(["respuesta" => "success", "msj" => "Enviado a DIAN"]);
            //if (true) {
                $soporte->cufe = $cuds;
                $soporte->save();
                foreach ($productos as $producto) {
                    $detalle = new Factura_detalles();
                    $detalle->cantidad = $producto->cantidad;
                    $detalle->valor = $producto->valor;
                    if (isset($producto->iva)) {
                        $detalle->iva = $producto->iva;
                    }
                    $detalle->productos_id = $producto->id;
                    $detalle->facturas_id = $soporte->id;
                    $detalle->save();
                }

                $datos = json_decode($request->input('datos'));
                foreach ($datos as $dato) {
                    if (!isset($dato->sumado)) {
                        $total = 0;
                        foreach ($datos as $dato1) {
                            if ($dato->id == $dato1->id) {
                                if ($dato->idtercero == $dato1->idtercero) {
                                    $dato1->sumado = 1;
                                    if ($dato->tipo != $dato1->tipo) {
                                        $total = $total - $dato1->valor;
                                    } else {
                                        $total = $total + $dato1->valor;
                                    }
                                } else {
                                    if (isset($dato1->extra)) {
                                        $dato1->sumado = 1;
                                        if ($dato->tipo != $dato1->tipo) {
                                            $total = $total - $dato1->valor;
                                        } else {
                                            $total = $total + $dato1->valor;
                                        }
                                    }
                                }
                            }
                        }
    
                        if ($total > 0) {
                            $cuenta = Cuenta::find($dato->id);
                            $movter = TerceroCahors::find($dato->idtercero);
                            $movimiento = new Movimiento();
                            $movimiento->naturaleza = $dato->tipo;
                            $movimiento->fecha = $soporte->fecha;
                            $movimiento->valor = $total;
                            $movimiento->concepto = $soporte->prefijo . " " . $soporte->numero . " " .  $cuenta->nombre;
                            $movimiento->cuentas_id = $cuenta->id;
                            $movimiento->facturas_id = $soporte->id;
                            $movimiento->terceros_id = $movter->id;
                            $movimiento->save();
                        } else {
                            $cruzar = true;
                        }
                    }
                }
                return json_encode(["respuesta" => "success", "msj" => $soporte->id]);
            } else {
                return json_encode(["respuesta" => "error", "msj" => "No se envió el soporte"]);
            }
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . $ex->getLine()]);
        }
    }

}
