<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Comprobante;
use App\Models\Cuenta;
use App\Models\Extra;
use App\Models\Factura;
use App\Models\Factura_detalles;
use App\Models\FacturasxRecibo;
use App\Models\FormaPago;
use App\Models\Movimiento;
use App\Models\Nota;
use App\Models\Producto;
use App\Models\Recibo;
use App\Models\Resolucion;
use App\Models\Retefuente;
use App\Models\Reteica;
use App\Models\Reteiva;
use App\Models\TerceroCahors;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade as PDF;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
use stdClass;
use Stenfrank\SoapDIAN\SOAPDIAN21;
use Stenfrank\UBL21dian\XAdES\SignInvoice;
use ZipArchive;

class ContabilidadController extends Controller
{
    public function listarCuentas(Request $request)
    {
        $cuentas = Cuenta::whereNull('cuentas_id')->get();

        return view('cuentas.lista', compact('cuentas'));
    }

    public function getNodos($nodo)
    {
        $cuentas = Cuenta::with('cuentas')->where('cuentas_id', $nodo)->get();

        return json_encode($cuentas);
    }

    public function infoNodo($nodo)
    {
        $cuenta = Cuenta::with('padre')->find($nodo);

        return json_encode($cuenta);
    }

    public function modificarCuentas(Request $request)
    {
        $cuenta = Cuenta::where('codigo', $request->input('codigo'))->first();
        if ($cuenta == null) {
            $cuenta = new Cuenta();
            $cuenta->total = 0;
        }
        $cuenta->codigo = $request->input('codigo');
        $cuenta->nombre = $request->input('descripcion');
        $cuenta->naturaleza = $request->input('naturaleza');
        if ($request->filled('padre')) {
            $cuenta->cuentas_id = Cuenta::where('codigo', $request->input('padre'))->first()->id;
        }
        $cuenta->save();

        return redirect('/contabilidad/plan_cuentas');
    }

    public function facturasVenta()
    {
        $facturas = Factura::with('credito', 'tercero')->where('tipo', 'Venta')->orderBy('id', 'desc')->Paginate(5);

        return view('facturas.listaVentas', compact('facturas'));
    }

    public function facturasCompra()
    {
        $fechaenv = Carbon::now();
        $envyear = $fechaenv->year;
        $facturas = Factura::with('tercero')->where(function($q) use($envyear){$q->where('year', $envyear)->where('prefijo', 'FC');})->orWhere('prefijo', 'FCA')->orderBy('id', 'desc')->paginate(5);

        return view('facturas.listaCompras', compact('facturas', 'fechaenv', 'envyear'));
    }

    public function nuevaCompra()
    {
        $productos = Producto::get();
        $retefuentes = Retefuente::get();
        $reteicas = Reteica::get();
        $reteivas= Reteiva::get();
        $extras = Extra::get();
        $fecha = Carbon::now();

        return view('facturas.formCompra', compact('productos', 'retefuentes', 'reteicas', 'reteivas', 'extras', 'fecha'));
    }

    public function getFacturaCompra(Request $request)
    {
        $factura = Factura::with('tercero')->find($request->input('factura'));

        return json_encode($factura);
    }

    public function getCalculosCompra(Request $request)
    {
        $datos = new stdClass();
        $predeterminado = TerceroCahors::where('documento', $request->input('predeterminado'))->first();
        $datos->predeterminado = $predeterminado;
        if ($request->filled('tercero')) {
            $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
            $datos->tercero = $tercero;
        } else {
            $datos->tercero = $predeterminado;
        }
        $producto = Producto::with('cuenta', 'contrapartida')->find($request->input('producto'));
        $datos->producto = $producto;
        if ($request->filled('retefuente')) {
            $retefuente = Retefuente::with('compra')->find($request->input('retefuente'));
            $datos->retefuente = $retefuente;
        }
        if ($request->filled('reteica')) {
            $reteica = Reteica::with('compra')->find($request->input('reteica'));
            $datos->reteica = $reteica;
        }
        if ($request->filled('reteiva')) {
            $reteiva = Reteiva::with('compra')->find($request->input('reteiva'));
            $datos->reteiva = $reteiva;
        }
        if ($request->filled('iva')) {
            $iva = Cuenta::find(171);
            $datos->iva = $iva;
        }
        if ($request->filled('exicas')) {
            $extras = Extra::with('compra')->whereIn('id', $request->input('exicas'))->get();
            $datos->extras = $extras;
        }

        return json_encode($datos);
    }

    public function guardarCompra(Request $request)
    {
        $cruzar = false;
        $hoy = Carbon::now();
        try {
            $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
            $factura = new Factura();
            $factura->fecha = $request->input('fecha');
            if ($request->input('tipof') == "Compra") {
                $factura->prefijo = "FC";
                $ultima = Factura::where('tipo', 'Compra')->where('prefijo', 'FC')->where('year', $hoy->year)->orderBy('numero', 'desc')->first();
            } else {
                $factura->prefijo = "FCA";
                $ultima = Factura::where('tipo', 'Compra')->where('prefijo', 'FCA')->orderBy('numero', 'desc')->first();
            }
            $factura->year = $hoy->year;
            $factura->descripcion = $request->input('concepto');
            $factura->valor = $request->input('total');
            $factura->tipo = "Compra";
            if ($ultima != null) {
                $factura->numero = $ultima->numero + 1;
            } else {
                $factura->numero = "1";
            }
            $factura->terceros_id = $tercero->id;
            $factura->save();

            $productos = json_decode($request->input('productos'));
            foreach ($productos as $producto) {
                $detalle = new Factura_detalles();
                $detalle->cantidad = $producto->cantidad;
                $detalle->valor = $producto->valor;
                if (isset($producto->iva)) {
                    $detalle->iva = $producto->iva;
                }
                $detalle->productos_id = $producto->id;
                $detalle->facturas_id = $factura->id;
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
                        $movimiento->fecha = $factura->fecha;
                        $movimiento->valor = $total;
                        $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .  $cuenta->nombre;
                        $movimiento->cuentas_id = $cuenta->id;
                        $movimiento->facturas_id = $factura->id;
                        $movimiento->terceros_id = $movter->id;
                        $movimiento->save();
                    } else {
                        $cruzar = true;
                    }
                }
            }

            if ($cruzar) {
                $factura->cruzada = 1;
                $factura->save();
            }
            return json_encode(["respuesta" => "success", "msj" => $factura->id]);
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage()]);
        }
    }

    public function detallesFactura($factura)
    {
        $factura = Factura::with(['movimientos' => function ($q) {
            $q->with('cuenta', 'tercero');
        }, 'tercero'])->find($factura);

        return view('facturas.detalles', compact('factura'));
    }

    public function nuevaVenta()
    {
        $productos = Producto::get();
        $retefuentes = Retefuente::get();
        $reteicas = Reteica::get();
        $extras = Extra::get();

        return view('facturas.formVenta', compact('productos', 'retefuentes', 'reteicas','extras'));
    }

    public function pagarCompra($factura)
    {
        $factura = Factura::with("productos.contrapartida")->find($factura);
        if ($factura->cruzada == 0) {
            $pagar = 0;
            $contras = [];
            foreach ($factura->productos as $producto) {
                if (!in_array($producto->contrapartida->id, $contras)) {
                    $movimiento = Movimiento::where('facturas_id', $factura->id)->where('cuentas_id', $producto->contrapartida->id)->first();
                    $pagar = $pagar + $movimiento->valor;
                    $contras[] = $producto->contrapartida->id;
                }
            }
            $formas = FormaPago::get();
            $abonos = Abono::where('facturas_id', $factura->id)->sum('valor');
            $pagar = $pagar - $abonos;

            return view('modulos.facturaCompra', compact('factura', 'formas', 'pagar'));
        } else {
            return redirect('/contabilidad/facturas/compras');
        }
    }

    public function asientoPagoCompra(Request $request)
    {
        $forma = FormaPago::find($request->input('forma'));
        $factura = Factura::with('productos.contrapartida', 'tercero')->find($request->input('factura'));
        $asiento = [];
        $contras = [];
        //$pagar = 0;
        $pago = $request->input('pago');
        foreach ($factura->productos as $producto) {
            if ($pago > 0) {
                if (!in_array($producto->contrapartida->id, $contras)) {
                    $abonos = Abono::where('facturas_id', $factura->id)->where('cuentas_id', $producto->contrapartida->id)->sum('valor');
                    $movimiento = Movimiento::where('facturas_id', $factura->id)->where('cuentas_id', $producto->contrapartida->id)->first();
                    if ($movimiento->valor > $abonos) {
                        if ($pago >= $movimiento->valor - $abonos) {
                            $asiento[] = (object) ["id" => $producto->contrapartida->id, "codigo" => $producto->contrapartida->codigo, "nombre" => $producto->contrapartida->nombre, "tipo" => "Débito", "valor" => $movimiento->valor - $abonos];
                            $pago = $pago - $movimiento->valor - $abonos;
                        } else {
                            $asiento[] = (object) ["id" => $producto->contrapartida->id, "codigo" => $producto->contrapartida->codigo, "nombre" => $producto->contrapartida->nombre, "tipo" => "Débito", "valor" => $pago];
                            $pago = 0;
                        }
                    }
                    //$pagar = $pagar + $movimiento->valor;
                    $contras[] = $producto->contrapartida->id;
                }
            }
        }
        $asiento[] = (object) ["id" => $forma->cuenta->id, "codigo" => $forma->cuenta->codigo, "nombre" => $forma->cuenta->nombre, "tipo" => "Crédito", "valor" => $request->input('pago')];

        return json_encode($asiento);
    }

    public function registrarPagoCompra(Request $request)
    {
        DB::beginTransaction();
        try {
            $factura = Factura::with('tercero')->find($request->input('factura'));
            if ($factura->cruzada == 1) {
                return json_encode(["respuesta" => "error", "msj" => "Esta factura ya fue cruzada"]);
            }
            $forma = FormaPago::find($request->input('forma'));
            $ahora = Carbon::now();
            $pago = $request->input('pago');

            if ($forma->prefijo == "Caja") {
                $prefijo = "EC1";
            } else {
                $prefijo = "EC2";
            }
            $ultimo = Comprobante::where('prefijo', $prefijo)->where('year', $ahora->year)->orderBy('id', 'desc')->first();
            $egreso = new Comprobante();
            if ($ultimo != null) {
                $egreso->numero = $ultimo->numero + 1;
            } else {
                $egreso->numero = 1;
            }

            $egreso->prefijo = $prefijo;
            $egreso->tipo = "Egreso";
            $egreso->valor = $pago;
            $egreso->concepto = "Egreso factura de compra " . $factura->prefijo . $factura->numero;
            $egreso->fecha = $ahora;
            $egreso->year = $ahora->year;
            $egreso->terceros_id = $factura->tercero->id;
            $egreso->facturas_id = $factura->id;
            $egreso->save();

            $movs = json_decode($request->input('movimientos'));
            foreach ($movs as $mov) {
                $cuenta = Cuenta::find($mov->id);
                $movimiento = new Movimiento();
                $movimiento->naturaleza = $mov->tipo;
                $movimiento->fecha = $ahora;
                $movimiento->valor = $mov->valor;
                $movimiento->concepto = $egreso->prefijo . " " . $egreso->numero . " " . $cuenta->nombre;
                $movimiento->cuentas_id = $cuenta->id;
                $movimiento->comprobantes_id = $egreso->id;
                $movimiento->terceros_id = $factura->tercero->id;
                $movimiento->save();

                if ($mov->tipo == "Débito") {
                    $abono = new Abono();
                    $abono->facturas_id = $factura->id;
                    $abono->cuentas_id = $cuenta->id;
                    $abono->valor = $mov->valor;
                    $abono->save();
                }
            }

            if ($pago >= $request->input('pagar')) {
                $factura->cruzada = 1;
            }
            $factura->save();

            DB::commit();

            return json_encode(["respuesta" => "success", "msj" => $egreso->id]);
        } catch (Exception $ex) {
            DB::rollBack();
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage()]);
        }
    }

    public function buscarCuenta(Request $request)
    {
        $cuenta = Cuenta::where('codigo', 'like', $request->input('cuenta') . "%")->orWhere('nombre', 'like', $request->input('cuenta') . "%")->get();

        return json_encode($cuenta);
    }

    public function getCalculosVenta(Request $request)
    {
        $datos = new stdClass();
        $producto = Producto::with('cuenta', 'contrapartida')->find($request->input('producto'));
        $datos->producto = $producto;
        if ($request->filled('retefuente')) {
            $retefuente = Retefuente::with('venta')->find($request->input('retefuente'));
            $datos->retefuente = $retefuente;
        }
        if ($request->filled('reteica')) {
            $reteica = Reteica::with('venta')->find($request->input('reteica'));
            $datos->reteica = $reteica;
        }
        if ($request->filled('iva')) {
            $iva = Cuenta::find(170);
            $datos->iva = $iva;
        }
        if ($request->filled('exicas')) {
            $extras = Extra::with('venta')->whereIn('id', $request->input('exicas'))->get();
            $datos->extras = $extras;
        }

        return json_encode($datos);
    }

    public function descargarCompra($factura)
    {
        $factura = Factura::with('movimientos.tercero')->find($factura);
        $fecha = Carbon::now();
        $dompdf = PDF::loadView('facturas.impresionCompra', compact('factura', 'fecha'));

        return $dompdf->stream($factura->prefijo . " " . $factura->numero .  ".pdf");
    }

    public function guardarVenta(Request $request)
    {
        $hoy = Carbon::now();
        try {
            $tercero = TerceroCahors::with('usuario', 'empresa')->where('documento', $request->input('tercero'))->first();
            $ultima = Factura::where('tipo', 'Venta')->where('prefijo', $request->input('formapago'))->orderBy('numero', 'desc')->first();
            $factura = new Factura();
            $factura->descripcion = $request->input('concepto');
            $factura->fecha = $hoy;
            $factura->placa = $request->input('placa');
            $factura->valor = $request->input('total');
            $factura->tipo = "Venta";
            $factura->year = $hoy->year;
            if ($ultima != null) {
                $factura->numero = $ultima->numero + 1;
            } else {
                $factura->numero = "1";
            }
            $factura->prefijo = $request->input('formapago');
            $resolucion = Resolucion::where('prefijo', $factura->prefijo)->first();
            $citec = $resolucion->citec;
            if ($factura->prefijo == "FECR") {
                $factura->formapago = "Crédito";
            } else {
                $factura->formapago = "Contado";
            }
            $factura->terceros_id = $tercero->id;

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
            $concatFact = $factura->prefijo . $factura->numero;
            $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatFact);
            $cufe = hash("sha384", $concatFact . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' .
                number_format($factura->valor - $iva, 2, ".", "") . '01' .
                number_format($iva, 2, ".", "") . '040.00030.00' . number_format($factura->valor, 2, ".", "") . '901318591' .
                $tercero->documento . $citec . '1');
            $qrcode = 'NroFactura=' . $concatFact . PHP_EOL .
                'NitFacturador=901318591' . PHP_EOL .
                'NitAdquiriente=' . $tercero->documento . PHP_EOL .
                'FechaFactura=' . $hoy->format("Y-m-d") . PHP_EOL .
                'ValorTotalFactura=' . $factura->valor . PHP_EOL .
                'CUFE=' . $cufe . PHP_EOL .
                'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cufe;
            $vencimiento = Carbon::parse($factura->fecha)->addMonth();
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
            $xmlView = view('facturas.ublGenerica', compact('factura', 'iva', 'siniva', 'baseiva', 'hoy', 'productos', 'tercero', 'vencimiento', 'finMes', 'cufe', 'segCode', 'qrcode', 'resolucion'))->render();

            $storePath = storage_path();
            if (!is_dir($storePath . "/facturas/" . $factura->prefijo . "/" . $concatFact)) {
                mkdir($storePath . "/facturas/" . $factura->prefijo . "/" . $concatFact);
            }
            $carpeta = $storePath . "/facturas/" . $factura->prefijo . "/" . $concatFact . "/";
            $xmlView = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $xmlView;
            $pathCertificate = $storePath . "/claves/Certificado.pfx";
            $passwors = '3ZDVyH24R3';
            $domDocument = new DOMDocument();
            $domDocument->loadXML($xmlView);
            $signInvoice = new SignInvoice($pathCertificate, $passwors, $xmlView);
            Storage::disk('facturas')->put("/" . $factura->prefijo . "/" . $concatFact . "/" . $concatFact . ".xml", $signInvoice->xml);

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
            $soapdian21->Action = 'http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillAsync';
            $soapdian21->startNodes($doc->saveXML());
            $xml = $soapdian21->soap;

            $headers = array(
                "Content-type: application/soap+xml;charset=\"utf-8\"",
                "SOAPAction: http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillAsync",
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

            $zipKey="";

            if ($response !== false) {
                
                $dom = new DOMDocument();
                $dom->loadXML($response);
                
                
                $xpath = new DOMXPath($dom);
                $xpath->registerNamespace('b', 'http://wcf.dian.colombia'); 
                
                // Buscar el nodo zipKey usando XPath
                $zipKeyNodes = $xpath->query('//b:zipKey');
                if ($zipKeyNodes->length > 0) {
                    $zipKey = $zipKeyNodes->item(0)->nodeValue;
                } 
            } 

            if (!empty($zipKey)) {
                //Si No está vacío
                /* guardamos el zip key en una lista para el command*/ 
            } else {
                // si está vacío

                /*consultar al ing caso sin zip key*/ 
            }

            Storage::disk('facturas')->put("/" . $factura->prefijo . "/" . $concatFact . "/" . $concatFact . "Response.xml", $response);
            $pos = strpos($response, "<b:StatusCode>");

            if (substr($response, $pos, 16) == "<b:StatusCode>00" || $factura->numero == '2838') {
                //if (true) {
                $factura->cufe = $cufe;
                $factura->save();
                foreach ($productos as $producto) {
                    $detalle = new Factura_detalles();
                    $detalle->cantidad = $producto->cantidad;
                    $detalle->valor = $producto->valor;
                    if (isset($producto->iva)) {
                        $detalle->iva = $producto->iva;
                    }
                    $detalle->productos_id = $producto->id;
                    $detalle->facturas_id = $factura->id;
                    $detalle->save();
                }

                $datos = json_decode($request->input('datos'));
                foreach ($datos as $dato) {
                    if (!isset($dato->sumado)) {
                        $total = 0;
                        foreach ($datos as $dato1) {
                            if ($dato->id == $dato1->id) {
                                $dato1->sumado = 1;
                                $total = $total + $dato1->valor;
                            }
                        }

                        $cuenta = Cuenta::find($dato->id);
                        $movimiento = new Movimiento();
                        $movimiento->naturaleza = $dato->tipo;
                        $movimiento->fecha = $hoy;
                        $movimiento->valor = $total;
                        $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .   $cuenta->nombre;
                        $movimiento->cuentas_id = $cuenta->id;
                        $movimiento->facturas_id = $factura->id;
                        $movimiento->terceros_id = $tercero->id;
                        $movimiento->save();
                    }
                }

                $qrGen = new BaconQrCodeGenerator();
                $imgqr = base64_encode($qrGen->size(100)->format('png')->generate($qrcode));
                $vencimientoMes = Carbon::parse($factura->fecha)->addMonth();
                $formater = new NumeroALetras();
                $letras = $formater->toWords($factura->valor, 2);
                $factura = Factura::with('tercero', 'productos', 'credito')->find($factura->id);
                PDF::loadView('notificaciones.facturaVenta', compact('factura', 'hoy', 'imgqr', 'vencimientoMes', 'letras', 'cufe', 'resolucion'))->save($carpeta . $concatFact . ".pdf");

                $zip = new ZipArchive();
                $zip->open($carpeta . $concatFact . "Email.zip", ZipArchive::CREATE);
                $zip->addFile($carpeta . $concatFact . ".xml", $concatFact . ".xml");
                $zip->addFile($carpeta . $concatFact . ".pdf", $concatFact . ".pdf");
                $zip->close();

                $to = $tercero->email;
                try {
                    Mail::send('notificaciones.emailFactura', compact('factura'), function ($message) use ($to, $carpeta, $concatFact) {
                        $message->from("notificaciones@apptaxcenter.com", "Cahors");
                        $message->to($to);
                        $message->bcc(["gestion@cahors.co"]);
                        $message->subject("Factura de Venta Cahors");
                        $message->attach($carpeta . $concatFact . "Email.zip", ['as' => 'Factura Electronica.zip', 'mime' => 'application/zip']);
                    });
                } catch (Exception $ex) {
                    $logFile = fopen($storePath . '/logCorreos.txt', 'a');
                    fwrite($logFile, "\n" . date("d/m/Y H:i:s") . $ex->getMessage());
                    fclose($logFile);
                }

                return json_encode(["respuesta" => "success", "msj" => $factura->id]);
            } else {
                return json_encode(["respuesta" => "error", "msj" => "No se envió la factura"]);
            }
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . $ex->getLine()]);
        }
    }

    public function descargarVenta($factura)
    {
        $factura = Factura::with(['credito' => function ($q) {
            $q->with(['costos' => function ($q) {
                $q->with('cuenta');
            }, 'cliente', 'placa']);
        }, 'tercero' => function ($q) {
            $q->with('usuario', 'empresa');
        }, 'productos'])->find($factura);
        $formater = new NumeroALetras();
        $letras = $formater->toWords($factura->valor, 2);
        $hora = Carbon::now();
        if ($factura->tercero->usuario != null) {
            $factura->tercero->direccion = $factura->tercero->usuario->direccion;
            $factura->tercero->municipio = $factura->tercero->usuario->municipio;
            $factura->tercero->email = $factura->tercero->usuario->email;
            $factura->tercero->celular = $factura->tercero->usuario->celular;
        } else {
            $factura->tercero->direccion = $factura->tercero->empresa->direccion;
            $factura->tercero->municipio = $factura->tercero->empresa->municipio;
            $factura->tercero->email = $factura->tercero->empresa->email;
            $factura->tercero->celular = $factura->tercero->empresa->telefono;
        }
        $dompdf = PDF::loadView('facturas.impresionVenta', compact('factura', 'letras', 'hora'));

        return $dompdf->stream("Factura Venta #" . $factura->prefijo . $factura->numero . ".pdf");
    }

    public function cobrarVenta($factura)
    {
        $factura = Factura::with("productos.contrapartida")->find($factura);
        if ($factura->cruzada == 0) {
            $ncreds = Nota::with('movimientos')->where('prefijo', 'NC')->where('facturas_id', $factura->id)->get();
            $recibo = Recibo::where('facturas_id', $factura->id)->where('estado', 'Activo')->orderBy('id', 'desc')->first();
            if ($recibo == null) {
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
                $cobrar = $recibo->saldo;
            }
            //$ncreds = Nota::where('prefijo', 'NC')->where('facturas_id', $factura->id)->get();
            $formas = FormaPago::get();
            $retefuentes = Retefuente::get();
            $reteicas = Reteica::get();
            $extras = Extra::get();
            return view('modulos.facturaVenta', compact('factura', 'formas', 'cobrar', 'retefuentes', 'reteicas', 'extras'));
        } else {
            return redirect('/contabilidad/facturas/ventas');
        }
    }

    public function asientoCobroVenta(Request $request)
    {
        $respuesta = new stdClass();
        $forma = FormaPago::find($request->input('forma'));
        $jsonfacturas = json_decode($request->input('facturas'));
        $facturas = [];
        foreach ($jsonfacturas as $json) {
            $fact = Factura::with('productos.contrapartida', 'tercero')->find(explode('-', $json->ide)[1]);
            $fact->abonar = $json->valor;
            $facturas[] = $fact;
        }
        $asiento = [];
        $ctmora = Cuenta::find(108);
        $base = 0;
        foreach ($facturas as $factura) {
            $contras = [];
            $pago = $factura->abonar;
            if ($factura->mora > 0) {
                if ($factura->abonar >= $factura->mora) {
                    $asiento[] = (object) ["id" => $ctmora->id, "codigo" => $ctmora->codigo, "nombre" => $ctmora->nombre, "tipo" => "Crédito", "valor" => $factura->mora, "mora" => 1];
                    $pago = $pago - $factura->mora;
                } else {
                    $asiento[] = (object) ["id" => $ctmora->id, "codigo" => $ctmora->codigo, "nombre" => $ctmora->nombre, "tipo" => "Crédito", "valor" => $factura->abonar, "mora" => 1];
                    $pago = 0;
                }
            }
            if ($pago > 0) {       
                foreach ($factura->productos as $producto) {
                    if (!in_array($producto->contrapartida->id, $contras)) {
                        $abonos = Abono::where('facturas_id', $factura->id)->where('cuentas_id', $producto->contrapartida->id)->sum('valor');
                        $movimiento = Movimiento::where('facturas_id', $factura->id)->where('cuentas_id', $producto->contrapartida->id)->first();
                        if ($movimiento != null) {
                            if ($movimiento->valor > $abonos) {
                                $base = $base + $producto->pivot->valor;
                                if ($pago >= $movimiento->valor - $abonos) {
                                    $asiento[] = (object) ["id" => $producto->contrapartida->id, "codigo" => $producto->contrapartida->codigo, "nombre" => $producto->contrapartida->nombre, "tipo" => "Crédito", "valor" => $movimiento->valor - $abonos, "factid" => $factura->id, "mora" => 0];
                                    $pago = $pago - $movimiento->valor - $abonos;
                                } else {
                                    $asiento[] = (object) ["id" => $producto->contrapartida->id, "codigo" => $producto->contrapartida->codigo, "nombre" => $producto->contrapartida->nombre, "tipo" => "Crédito", "valor" => $pago, "factid" => $factura->id, "mora" => 0];
                                    break;
                                }
                            }
                        } else {
                            $base = $base + $producto->pivot->valor;
                            $asiento[] = (object) ["id" => $producto->contrapartida->id, "codigo" => $producto->contrapartida->codigo, "nombre" => $producto->contrapartida->nombre, "tipo" => "Crédito", "valor" => $pago, "factid" => $factura->id, "mora" => 0];
                            break;
                        }
                        $contras[] = $producto->contrapartida->id;
                    }
                }
            }
        }

        $cpartida = $request->input('valor');
        $retenciones = 0;
        if ($request->filled('retefuente')) {
            $retefuente = Retefuente::with('venta')->find($request->input('retefuente'));
            $fue = $base * ($retefuente->porcentaje / 100);
            $retenciones = $retenciones + $fue;
            $cpartida = $cpartida - $fue;
            $asiento[] = (object) ["id" => $retefuente->venta->id, "codigo" => $retefuente->venta->codigo, "nombre" => $retefuente->venta->nombre, "tipo" => "Débito", "valor" => $fue, "mora" => 0];
        }

        if ($request->filled('reteica')) {
            $reteica = Reteica::with('venta')->find($request->input('reteica'));
            $ica = $base * ($reteica->porcentaje / 100);
            $retenciones = $retenciones + $ica;

            $asiento[] = (object) ["id" => $reteica->venta->id, "codigo" => $reteica->venta->codigo, "nombre" => $reteica->venta->nombre, "tipo" => "Débito", "valor" => $ica, "mora" => 0];
            if ($request->filled('exicas')) {
                $extras = Extra::with('venta')->whereIn('id', $request->input('exicas'))->get();
                $cpartida = $cpartida - $ica;
                $sumaExtras = 0;
                foreach ($extras as $extra) {
                    $ext = $ica * ($extra->porcentaje / 100);
                    $sumaExtras = $sumaExtras + $ext;
                    $cpartida = $cpartida - $ext;
                    $asiento[] = (object) ["id" => $extra->venta->id, "codigo" => $extra->venta->codigo, "nombre" => $extra->venta->nombre, "tipo" => "Débito", "valor" => $ext, "mora" => 0];
                }
                $retenciones = $retenciones + $sumaExtras;
            }
        }

        $asiento[] = (object) ["id" => $forma->cuenta->id, "codigo" => $forma->cuenta->codigo, "nombre" => $forma->cuenta->nombre, "tipo" => "Débito", "valor" => $cpartida, "mora" => 0];

        if ($request->filled('ajuste')) {
            $ajuste = $request->input('ajuste');
            if ($ajuste > 0) {
                $ctaAjuste = Cuenta::find(282);
                $asiento[] = (object) ["id" => $ctaAjuste->id, "codigo" => $ctaAjuste->codigo, "nombre" => $ctaAjuste->nombre, "tipo" => "Crédito", "factid" => $factura->id, "valor" => $ajuste, "mora" => 0];
            } else {
                $ctaAjuste = Cuenta::find(164);
                $asiento[] = (object) ["id" => $ctaAjuste->id, "codigo" => $ctaAjuste->codigo, "nombre" => $ctaAjuste->nombre, "tipo" => "Débito", "valor" => $ajuste * -1, "mora" => 0];
            }
        }

        $respuesta->asiento = $asiento;
        $respuesta->retenciones = $retenciones;

        return json_encode($respuesta);
    }

    public function registrarCobroVenta(Request $request)
    {
        try {
            $ahora = Carbon::now();
            $retenido = $request->input('retenciones');
            $forma = FormaPago::find($request->input('forma'));
            $ajuste = $request->input('ajuste');

            $recibo = new Recibo();
            $ultimo = Recibo::where('prefijo', 'RC1')->where('year', $ahora->year)->where('fecha', '>=', '2023-01-05')->orderBy('numero', 'desc')->first();
            if ($ultimo != null) {
                $recibo->numero = $ultimo->numero + 1;
            } else {
                $recibo->numero = 1;
            }
            $recibo->prefijo = "RC1";
            $recibo->valor = $request->input('valor') - $retenido;
            $recibo->retenciones = $retenido;
            $recibo->fecha = $ahora;
            $recibo->year = $ahora->year;
            $recibo->observaciones = $request->input('observaciones');
            $recibo->formas_pago_id = $forma->id;
            $recibo->save();

            $jsonfacturas = json_decode($request->input('facturas'));
            $facturas = [];
            foreach ($jsonfacturas as $json) {
                $fact = Factura::with('tercero')->find(explode('-', $json->ide)[1]);
                $fact->abonar = $json->valor;
                $fact->original = $json->original;
                $facturas[] = $fact;
            }
            foreach ($facturas as $factura) {
                $factxrec = new FacturasxRecibo();
                $factxrec->facturas_id = $factura->id;
                $factxrec->recibos_id = $recibo->id;
                if ($factura->mora > 0) {
                    $factura->fecha_mora = $ahora;
                    if ($factura->abonar > $factura->mora) {
                        $factxrec->mora = $factura->mora;
                        $factxrec->saldo = $factura->original - $factura->abonar;
                        $factura->mora = 0;
                    } else {
                        $factxrec->mora = $factura->abonar;
                        $factxrec->saldo = $factura->original;
                    }
                } else {
                    $factxrec->mora = 0;
                    $factxrec->saldo = $factura->original - $factura->abonar;
                }
                $factxrec->abono = $factura->abonar;
                $factxrec->save();
                $factura->saldo = $factxrec->saldo;
                if ($factura->saldo <= 0) {
                    $factura->cruzada = 1;
                }
                unset($factura->original);
                unset($factura->abonar);
                $factura->save();
            }

            $movs = json_decode($request->input('movimientos'));
            foreach ($movs as $mov) {
                $cuenta = Cuenta::find($mov->id);
                $movimiento = new Movimiento();
                $movimiento->naturaleza = $mov->tipo;
                $movimiento->fecha = $ahora;
                $movimiento->valor = $mov->valor;
                $movimiento->cuentas_id = $cuenta->id;
                $movimiento->recibos_id = $recibo->id;
                $movimiento->terceros_id = $factura->tercero->id;
                if ($mov->tipo == "Crédito" && $mov->mora != 1) {
                    $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . " " . $cuenta->nombre;
                    if (isset($mov->fact_id)) {
                        $abono = new Abono();
                        $abono->valor = $movimiento->valor;
                        $abono->cuentas_id = $movimiento->cuentas_id;
                        $abono->facturas_id = $mov->factid;
                        $abono->save();
                    }
                } else {
                    $movimiento->concepto = $recibo->prefijo . " " . $recibo->numero . ". " . $recibo->observaciones;
                }
                $movimiento->save();
            }

            return json_encode(["respuesta" => "success", "msj" => $recibo->id]);
        } catch (Exception $ex) {
            return json_encode(["respuesta" => "error", "msj" => $ex->getMessage() . "--" . $ex->getLine()]);
        }
    }

    public function enviarFactura()
    {
        $factura = Factura::with('tercero', 'productos', 'credito')->find(0);
        $concatFact = $factura->prefijo . $factura->numero;
        $hoy = Carbon::now();
        $cufe = "caf9b541e40f1fb73bf5bb7a9fa10c1102b005df5f55c2e1f8c73472827c88347dd36fb7fb59164863411fd2b41a4b6f";
        $qrcode = "NroFactura=FECR464
        NitFacturador=901318591
        NitAdquiriente=860022137
        FechaFactura=2022-01-17
        ValorTotalFactura=67620.56
        CUFE=caf9b541e40f1fb73bf5bb7a9fa10c1102b005df5f55c2e1f8c73472827c88347dd36fb7fb59164863411fd2b41a4b6f
        URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=caf9b541e40f1fb73bf5bb7a9fa10c1102b005df5f55c2e1f8c73472827c88347dd36fb7fb59164863411fd2b41a4b6f";
        $storePath = storage_path();
        $carpeta = $storePath . "/facturas/" . $factura->prefijo . "/" . $concatFact . "/";
        $qrGen = new BaconQrCodeGenerator();
        $imgqr = base64_encode($qrGen->size(100)->format('png')->generate($qrcode));
        $vencimientoMes = Carbon::parse($factura->fecha)->addMonth();
        $formater = new NumeroALetras();
        $letras = $formater->toWords($factura->valor, 2);
        PDF::loadView('notificaciones.facturaVenta', compact('factura', 'hoy', 'imgqr', 'vencimientoMes', 'letras', 'cufe'))->save($carpeta . $concatFact . ".pdf");

        $zip = new ZipArchive();
        $zip->open($carpeta . $concatFact . "Email.zip", ZipArchive::CREATE);
        $zip->addFile($carpeta . $concatFact . ".xml", $concatFact . ".xml");
        $zip->addFile($carpeta . $concatFact . ".pdf", $concatFact . ".pdf");
        $zip->close();

        if ($factura->tercero->empresa != null) {
            $to = $factura->tercero->empresa->email;
        } else {
            $to = $factura->tercero->usuario->email;
        }

        try {
            Mail::send('notificaciones.emailFactura', compact('factura'), function ($message) use ($to, $carpeta, $concatFact) {
                $message->from("notificaciones@apptaxcenter.com", "Cahors");
                $message->to($to);
                $message->bcc(["gestion@cahors.co"]);
                $message->subject("Factura de Venta Cahors");
                $message->attach($carpeta . $concatFact . "Email.zip", ['as' => 'Factura Electronica.zip', 'mime' => 'application/zip']);
            });
        } catch (Exception $ex) {
            $logFile = fopen($storePath . '/logCorreos.txt', 'a');
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . $ex->getMessage());
            fclose($logFile);
        }

        return "enviada";
    }
}
