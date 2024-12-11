<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Credito;
use App\Models\Cuenta;
use App\Models\Cuota;
use App\Models\Factura;
use App\Models\Factura_detalles;
use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Resolucion;
use App\Models\Seguro;
use App\Models\TerceroCahors;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
use Stenfrank\SoapDIAN\SOAPDIAN21;
use Stenfrank\UBL21dian\XAdES\SignInvoice;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class CarteraController extends Controller
{
    public function listarCartera()
    {
        $clientes = User::with(['creditos'=>function($q){$q->with('cuotas', function($r){$r->where('estado', 'Vencida');});}])->whereHas('creditos', function($q){$q->whereHas('cuotas', function($r){$r->where('estado', 'Vencida');});})->get();

        return view('cartera.lista', compact('clientes'));
    }

    public function cambiarMora($cuota)
    {
        $cuota = Cuota::with('credito')->find($cuota);
        if($cuota->estado_mora == 1){
            $cuota->estado_mora = 0;
            $cuota->saldo_mora = 0;
        }else{
            $cuota->estado_mora = 1;
        }
        $cuota->save();

        return redirect('creditos/' . $cuota->credito->id . '/plan_pagos');
    }

    public function editarMora(Request $request){
        $cuota = Cuota::with('credito')->find($request->input('idcuota'));
        $cuota->saldo_mora = $request->input('esaldomora');
        $cuota->save();

        return redirect('creditos/' . $cuota->credito->id . '/plan_pagos');
    }

    public function editarTotalMora(Request $request)
    {
        $i=1;
        $cuotas = Cuota::with('credito')->where('creditos_id', $request->input('idcredito'))->where('estado', 'Vencida')->get();
        $numCuotas = count($cuotas);
        foreach ($cuotas as $cuota) {
            if($i < $numCuotas){
                $cuota->saldo_mora = 0;
                
            }
            else if($i == $numCuotas){
                $cuota->saldo_mora = $request->input('esaldomoraTotal');

            }
            $cuota->save();
            $i++;
        }
        return redirect('creditos/' . $request->input('idcredito') . '/plan_pagos');  
    }


    public function listarSeguros()
    {
        $seguros = Seguro::with('facturas', 'tercero')->orderBy('id', 'desc')->paginate(10);

        return view('seguros.lista', compact('seguros'));
    }

    public function inactivarSeguro($seguro)
    {
        $seguro = Seguro::find($seguro);
        $seguro->estado = "Inactivo";
        $seguro->save();

        return redirect('/seguros');
    }

    public function registrarSeguro(Request $request)
    {
        try {
            $hoy = Carbon::now();
            $otros = $request->input('costos');
            $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
            $seguro = new Seguro();
            $seguro->estado = "Activo";
            $seguro->valor = $request->input('valor');
            $seguro->interes = $request->input('interes');
            $seguro->costos = $otros;
            $seguro->fecha = $hoy;
            $seguro->vencimiento = $request->input('vencimiento');
            $seguro->terceros_id = $tercero->id;
            if($request->filled('pagadas')){
                $seguro->pagadas = $request->input('pagadas');
            }else{
                $seguro->pagadas = 0;
            }
            
            $ultima = Factura::where('tipo', 'Venta')->where('prefijo', 'FECR')->orderBy('numero', 'desc')->first();
            $factura = new Factura();
            $factura->descripcion = "Seguro de vida. Mes " . ($seguro->pagadas + 1);
            $factura->fecha = $hoy;
            $factura->tipo = "Venta";
            if($ultima != null){
                $factura->numero = $ultima->numero + 1;
            }else{
                $factura->numero = "1";
            }
            $factura->prefijo = "FECR";
            $factura->formapago = "Crédito";
            $factura->terceros_id = $tercero->id;
            $resolucion = Resolucion::where('prefijo', $factura->prefijo)->first();
            $iva = 0;
            $baseiva = 0;
            $siniva = 0;
            $costos = Costo::whereIn('id', [5,6,7])->get();
            $productos = [];
            for ($i=0; $i < count($costos); $i++) { 
                $prod = Producto::where('cuentas_id', $costos[$i]->cuentas_id)->first();
                if($costos[$i]->iva == 1){
                    $baseiva = $baseiva + (($otros*$costos[$i]->porcentaje)/100);
                    $prod->iva = (($otros*$costos[$i]->porcentaje)/100)*0.19;
                    $iva = $iva + $prod->iva;
                }else{
                    $siniva = $siniva + (($otros*$costos[$i]->porcentaje)/100); 
                }
                $prod->valor = ($otros*$costos[$i]->porcentaje)/100;
                $prod->cantidad = 1;
                $productos[] = $prod;
            }
            $productos2 = Producto::with('cuenta')->whereIn('id', [27,44])->get();
            $productos2[0]->valor = $seguro->interes;
            $productos2[0]->cantidad = 1;
            $productos[] = $productos2[0];
            $productos2[1]->valor = $seguro->valor;
            $productos2[1]->cantidad = 1;
            $productos[] = $productos2[1];
            $siniva = $siniva + $seguro->valor + $seguro->interes;
            $factura->valor = $siniva + $baseiva + $iva;
    
            $concatFact = $factura->prefijo.$factura->numero;
            $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatFact);
            $cufe = hash("sha384", $concatFact . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' . 
            number_format($factura->valor-$iva, 2, ".", "") . '01' . 
            number_format($iva, 2, ".", "") . '040.00030.00' . number_format($factura->valor, 2, ".", "") . '901318591' . 
            $tercero->documento . $resolucion->citec . '1');
            $qrcode = 'NroFactura=' . $concatFact . PHP_EOL .
            'NitFacturador=901318591' . PHP_EOL .
            'NitAdquiriente=' . $tercero->documento . PHP_EOL .
            'FechaFactura=' . $hoy->format("Y-m-d") . PHP_EOL .
            'ValorTotalFactura=' . $factura->valor . PHP_EOL .
            'CUFE=' . $cufe . PHP_EOL .
            'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cufe;
            $vencimiento = Carbon::parse($seguro->vencimiento);
            $finMes =  Carbon::now()->lastOfMonth();
            if($tercero->usuario != null){
                $tercero->municipio = $tercero->usuario->municipio;
                $tercero->direccion = $tercero->usuario->direccion;
                $tercero->email = $tercero->usuario->email;
            }else{
                $tercero->municipio = $tercero->empresa->municipio;
                $tercero->direccion = $tercero->empresa->direccion;
                $tercero->email = $tercero->empresa->email;
            }
            $xmlView = view('facturas.ublGenerica', compact('factura', 'iva', 'siniva', 'baseiva', 'hoy', 'productos', 'tercero', 'vencimiento', 'finMes', 'cufe', 'segCode', 'qrcode', 'resolucion'))->render();
            
            $storePath = storage_path();
            $carpeta = $storePath . "/facturas/" . $factura->prefijo . "/" . $concatFact . "/";
    
            $xmlDian = $this->firmarFactura($storePath, $concatFact, $xmlView, $factura->prefijo, $carpeta);
            $response = $this->enviarDIAN($xmlDian, $factura->prefijo, $concatFact);
            $pos = strpos($response, "<b:StatusCode>");
            if(substr($response, $pos, 16) == "<b:StatusCode>00"){
            //if(true){
                $seguro->facturadas = $seguro->pagadas + 1;
                $seguro->save();
                $factura->seguros_id = $seguro->id;
                $factura->cufe = $cufe;
                $factura->vencimiento = $seguro->vencimiento;
                $factura->fecha_mora = $seguro->vencimiento;
                $factura->saldo = $factura->valor;
                $factura->save();
                foreach ($productos as $producto) {
                    $detalle = new Factura_detalles();
                    $detalle->cantidad = $producto->cantidad;
                    $detalle->valor = $producto->valor;
                    if(isset($producto->iva)){
                        $detalle->iva = $producto->iva;
                    } 
                    $detalle->productos_id = $producto->id;
                    $detalle->facturas_id = $factura->id;
                    $detalle->save();

                    $movimiento = new Movimiento();
                    $movimiento->naturaleza = "Crédito";
                    $movimiento->fecha = $hoy;
                    $movimiento->valor = $producto->valor;
                    $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " . $producto->cuenta->nombre;
                    $movimiento->cuentas_id = $producto->cuenta->id;
                    $movimiento->facturas_id = $factura->id;
                    $movimiento->terceros_id = $tercero->id;
                    $movimiento->save();
                }
    
                $civa = Cuenta::find(170);
                if($civa != null){
                    $movimiento = new Movimiento();
                    $movimiento->fecha = $hoy;
                    $movimiento->naturaleza = "Crédito";
                    $movimiento->valor = $iva;
                    $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " . $civa->nombre;
                    $movimiento->cuentas_id = $civa->id;
                    $movimiento->facturas_id = $factura->id;
                    $movimiento->terceros_id = $tercero->id;
                    $movimiento->save();
                }
    
                $cclientes = Cuenta::find(212);
                if($cclientes != null){
                    $movimiento = new Movimiento();
                    $movimiento->fecha = $hoy;
                    $movimiento->naturaleza = "Débito";
                    $movimiento->valor = $factura->valor;
                    $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " . $cclientes->nombre;
                    $movimiento->cuentas_id = $cclientes->id;
                    $movimiento->facturas_id = $factura->id;
                    $movimiento->terceros_id = $tercero->id;
                    $movimiento->save();
                }
    
                $qrGen = new BaconQrCodeGenerator();
                $imgqr = base64_encode($qrGen->size(100)->format('png')->generate($qrcode));
                if($factura->vencimiento == null){
                    $vencimientoMes = Carbon::parse($factura->fecha)->addMonth();
                }else{
                    $vencimientoMes = Carbon::parse($factura->vencimiento);
                }
                $formater = new NumeroALetras();
                $letras = $formater->toWords($factura->valor, 2);
                $factura = Factura::with('tercero', 'productos', 'credito')->find($factura->id);
                PDF::loadView('notificaciones.facturaVenta', compact('factura', 'hoy', 'imgqr', 'vencimientoMes', 'letras', 'cufe', 'resolucion'))->save($carpeta . $concatFact . ".pdf");
                $this->enviarEmail($tercero->email, $factura, $carpeta, $concatFact, $storePath);
                
                return redirect('/contabilidad/facturas/ventas/' . $factura->id . '/imprimir');
            }else{
                return back()->with('error', 'La factura no pudo ser generada');
            }
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        } 
    }

    public function editarSeguro(Request $request){

        $seguro = Seguro::find($request->input('idseguro'));
        $seguro->valor = $request->input('edivalor');
        $seguro->interes = $request->input('ediinteres');
        $seguro->costos = $request->input('edicostos');
        $seguro->vencimiento = $request->input('edivencimiento');
        $seguro->save();

        return redirect('/seguros');

    }

    public function enviarDIAN($xml, $prefijo, $concatFact)
    {
        $headers = array(
            "Content-type: application/soap+xml;charset=\"utf-8\"",
            "SOAPAction: http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync", 
            "Content-length: " . strlen($xml),
            "Host: vpfe.dian.gov.co",
            "Connection: Keep-Alive"); 
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
        Storage::disk('facturas')->put("/". $prefijo . "/" . $concatFact . "/" . $concatFact . "Response.xml", $response);
    
        return $response;
    }

    public function enviarEmail($to, $factura, $carpeta, $concatFact, $storePath)
    {
        $zip = new ZipArchive();
        $zip->open($carpeta . $concatFact . "Email.zip", ZipArchive::CREATE);
        $zip->addFile($carpeta . $concatFact . ".xml", $concatFact . ".xml");
        $zip->addFile($carpeta . $concatFact . ".pdf", $concatFact . ".pdf");
        $zip->close();
        try {
            Mail::send('notificaciones.emailFactura', compact('factura'), function ($message) use($to, $carpeta, $concatFact){
                $message->from("notificaciones@apptaxcenter.com", "Cahors");
                $message->to($to);
                $message->bcc(["gestion@cahors.co"]);
                $message->subject("Factura de Venta Cahors");
                $message->attach($carpeta . $concatFact . "Email.zip", ['as' => 'Factura Electronica.zip', 'mime' => 'application/zip']);
            });
        } catch (Exception $ex) {
            $logFile = fopen($storePath . '/logCorreos.txt', 'a');
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). $ex->getMessage());
            fclose($logFile);
        }
    }

    public function firmarFactura($storePath, $concatFact, $xmlView, $prefijo, $carpeta)
    {
        if(!is_dir($storePath . "/facturas/" . $prefijo . "/" . $concatFact)){
            mkdir($storePath . "/facturas/" . $prefijo . "/" . $concatFact);
        }
        $xmlView = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL . $xmlView;
        $pathCertificate = $storePath . "/claves/Certificado.pfx";
        $passwors = '3ZDVyH24R3';
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlView);
        $signInvoice = new SignInvoice($pathCertificate, $passwors, $xmlView);
        Storage::disk('facturas')->put("/". $prefijo . "/" . $concatFact . "/" . $concatFact . ".xml", $signInvoice->xml);
        
        $zip = new ZipArchive();
        $zip->open($carpeta . $concatFact . ".zip", ZipArchive::CREATE);
        $zip->addFile($carpeta . $concatFact . ".xml", $concatFact . ".xml");
        $zip->close();

        $numfact = $concatFact . ".zip";
        $contenido = base64_encode(file_get_contents($carpeta . $concatFact . ".zip"));
        $xmlPeticion = view('facturas.facturaPeticion', compact('numfact', 'contenido'))->render();

        $doc = new DOMDocument();
        $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?>'. $xmlPeticion);
        $soapdian21 = new SOAPDIAN21($storePath . "/claves/Certificado.pfx", "3ZDVyH24R3");
        $soapdian21->Action = 'http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync';
        $soapdian21->startNodes($doc->saveXML());

        return $soapdian21->soap;
    }

    public function renovarSeguros(Request $request)
    {
        $seguros = Seguro::with('facturas', 'tercero')->where('estado', 'Activo')->get();

        return view('seguros.renovar', compact('seguros'));
    }

    public function facturarSeguros(Request $request)
    {
        $arraySeguros = $request->input('seguros');
        $concepto = $request->input('concepto');
        if($arraySeguros != null){
            $seguros = Seguro::with('tercero')->whereIn('id', $request->input('seguros'))->get();
            try {
                foreach ($seguros as $seguro) {
                    $seguro->vencimiento = Carbon::parse($seguro->vencimiento)->addMonth();
                    $seguro->facturadas = $seguro->facturadas + 1;
                    $tercero = $seguro->tercero;
                    $otros = $seguro->costos;
                    $hoy = Carbon::now();
                    $ultima = Factura::where('tipo', 'Venta')->where('prefijo', 'FECR')->orderBy('numero', 'desc')->first();
                    $factura = new Factura();
                    $factura->descripcion = "Seguro de vida. " . $concepto;
                    $factura->fecha = $hoy;
                    $factura->tipo = "Venta";
                    if($ultima != null){
                        $factura->numero = $ultima->numero + 1;
                    }else{
                        $factura->numero = "1";
                    }
                    $factura->prefijo = "FECR";
                    $factura->formapago = "Crédito";
                    $factura->terceros_id = $tercero->id;
                    $factura->vencimiento = $seguro->vencimiento;
                    $resolucion = Resolucion::where('prefijo', $factura->prefijo)->first();
                    $iva = 0;
                    $baseiva = 0;
                    $siniva = 0;
                    $costos = Costo::whereIn('id', [5,6,7])->get();
                    $productos = [];
                    for ($i=0; $i < count($costos); $i++) { 
                        $prod = Producto::where('cuentas_id', $costos[$i]->cuentas_id)->first();
                        if($costos[$i]->iva == 1){
                            $baseiva = $baseiva + (($otros*$costos[$i]->porcentaje)/100);
                            $prod->iva = (($otros*$costos[$i]->porcentaje)/100)*0.19;
                            $iva = $iva + $prod->iva;
                        }else{
                            $siniva = $siniva + (($otros*$costos[$i]->porcentaje)/100); 
                        }
                        $prod->valor = ($otros*$costos[$i]->porcentaje)/100;
                        $prod->cantidad = 1;
                        $productos[] = $prod;
                    }
                    $productos2 = Producto::with('cuenta')->whereIn('id', [27,44])->get();
                    $productos2[0]->valor = $seguro->interes;
                    $productos2[0]->cantidad = 1;
                    $productos[] = $productos2[0];
                    $productos2[1]->valor = $seguro->valor;
                    $productos2[1]->cantidad = 1;
                    $productos[] = $productos2[1];
                    $siniva = $siniva + $seguro->valor + $seguro->interes;
                    $factura->valor = $siniva + $baseiva + $iva;
        
                    $concatFact = $factura->prefijo.$factura->numero;
                    $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatFact);
                    $cufe = hash("sha384", $concatFact . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' . 
                    number_format($factura->valor-$iva, 2, ".", "") . '01' . 
                    number_format($iva, 2, ".", "") . '040.00030.00' . number_format($factura->valor, 2, ".", "") . '901318591' . 
                    $tercero->documento . $resolucion->citec . '1');
                    $qrcode = 'NroFactura=' . $concatFact . PHP_EOL .
                    'NitFacturador=901318591' . PHP_EOL .
                    'NitAdquiriente=' . $tercero->documento . PHP_EOL .
                    'FechaFactura=' . $hoy->format("Y-m-d") . PHP_EOL .
                    'ValorTotalFactura=' . $factura->valor . PHP_EOL .
                    'CUFE=' . $cufe . PHP_EOL .
                    'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cufe;
                    $vencimiento = $factura->vencimiento;
                    $finMes =  Carbon::now()->lastOfMonth();
                    if($tercero->usuario != null){
                        $tercero->municipio = $tercero->usuario->municipio;
                        $tercero->direccion = $tercero->usuario->direccion;
                        $tercero->email = $tercero->usuario->email;
                    }else{
                        $tercero->municipio = $tercero->empresa->municipio;
                        $tercero->direccion = $tercero->empresa->direccion;
                        $tercero->email = $tercero->empresa->email;
                    }
                    $xmlView = view('facturas.ublGenerica', compact('factura', 'iva', 'siniva', 'baseiva', 'hoy', 'productos', 'tercero', 'vencimiento', 'finMes', 'cufe', 'segCode', 'qrcode', 'resolucion'))->render();
                    
                    $storePath = storage_path();
                    $carpeta = $storePath . "/facturas/" . $factura->prefijo . "/" . $concatFact . "/";
    
                    $xmlDian = $this->firmarFactura($storePath, $concatFact, $xmlView, $factura->prefijo, $carpeta);
                    $response = $this->enviarDIAN($xmlDian, $factura->prefijo, $concatFact);
                    $pos = strpos($response, "<b:StatusCode>");
                    if(substr($response, $pos, 16) == "<b:StatusCode>00"){
                    //if(true){
                        $seguro->save();
                        $factura->seguros_id = $seguro->id;
                        $factura->cufe = $cufe;
                        $factura->fecha_mora = $factura->vencimiento;
                        $factura->saldo = $factura->valor;
                        $factura->save();
                        foreach ($productos as $producto) {
                            $detalle = new Factura_detalles();
                            $detalle->cantidad = $producto->cantidad;
                            $detalle->valor = $producto->valor;
                            if(isset($producto->iva)){
                                $detalle->iva = $producto->iva;
                            } 
                            $detalle->productos_id = $producto->id;
                            $detalle->facturas_id = $factura->id;
                            $detalle->save();
        
                            $movimiento = new Movimiento();
                            $movimiento->naturaleza = "Crédito";
                            $movimiento->fecha = $hoy;
                            $movimiento->valor = $producto->valor;
                            $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " . $producto->cuenta->nombre;
                            $movimiento->cuentas_id = $producto->cuenta->id;
                            $movimiento->facturas_id = $factura->id;
                            $movimiento->terceros_id = $tercero->id;
                            $movimiento->save();
                        }
        
                        $civa = Cuenta::find(170);
                        if($civa != null){
                            $movimiento = new Movimiento();
                            $movimiento->fecha = $hoy;
                            $movimiento->naturaleza = "Crédito";
                            $movimiento->valor = $iva;
                            $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " . $civa->nombre;
                            $movimiento->cuentas_id = $civa->id;
                            $movimiento->facturas_id = $factura->id;
                            $movimiento->terceros_id = $tercero->id;
                            $movimiento->save();
                        }
            
                        $cclientes = Cuenta::find(212);
                        if($cclientes != null){
                            $movimiento = new Movimiento();
                            $movimiento->fecha = $hoy;
                            $movimiento->naturaleza = "Débito";
                            $movimiento->valor = $factura->valor;
                            $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " . $cclientes->nombre;
                            $movimiento->cuentas_id = $cclientes->id;
                            $movimiento->facturas_id = $factura->id;
                            $movimiento->terceros_id = $tercero->id;
                            $movimiento->save();
                        }
        
                        $qrGen = new BaconQrCodeGenerator();
                        $imgqr = base64_encode($qrGen->size(100)->format('png')->generate($qrcode));
                        if($factura->vencimiento == null){
                            $vencimientoMes = Carbon::parse($factura->fecha)->addMonth();
                        }else{
                            $vencimientoMes = Carbon::parse($factura->vencimiento);
                        }
                        $formater = new NumeroALetras();
                        $letras = $formater->toWords($factura->valor, 2);
                        $factura = Factura::with('tercero', 'productos', 'credito')->find($factura->id);
                        PDF::loadView('notificaciones.facturaVenta', compact('factura', 'hoy', 'imgqr', 'vencimientoMes', 'letras', 'cufe', 'resolucion'))->save($carpeta . $concatFact . ".pdf");
                        $this->enviarEmail($tercero->email, $factura, $carpeta, $concatFact, $storePath);
                                   
                    }else{
                        return back()->with('error', 'Algunas facturas no pudieron ser emitidas');
                    }
                } 
            } catch (Exception $ex) {
                return back()->with('error', $ex->getMessage() . "---" . $ex->getLine());
            }

            return redirect('/contabilidad/facturas/ventas');
        }else{
            return redirect('/contabilidad/facturas/ventas');
        } 
    }

    public function prefacturaVista()
    {
        return view('seguros.prefacturaForm');
    }

    public function descargarPrefactura(Request $request)
    {
        $fechaInicial = Carbon::parse($request->input('inimes') . '-01');
        $fechaAux = clone $fechaInicial;
        $fechaFinal = Carbon::parse($request->input('finmes') . '-01')->lastOfMonth();
        $diferenciaMeses = $fechaFinal->diffInMonths($fechaInicial);

        $rango = [$fechaInicial->format('Y-m-d'), $fechaFinal->format('Y-m-d')];

        $seguros = Seguro::with(['facturas'=>function($q) use($rango){$q->whereBetween('vencimiento', $rango);},'tercero'])->where('estado', 'Activo')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]];
        $sheet->getStyle("A1:Z1")->applyFromArray($styleCentrar);

        $sheet->setCellValue("A1", "Documento");
        $sheet->setCellValue("B1", "Nombre");
        $sheet->setCellValue("C1", "Estado");
        $sheet->setCellValue("D1", "Fecha de Afiliación");
        $sheet->setCellValue("E1", "Pago");

        $col = 6;
        for ($i=0; $i <= $diferenciaMeses; $i++) { 
            $sheet->mergeCellsByColumnAndRow($col, 1, $col+1, 1);
            $sheet->setCellValueByColumnAndRow($col, 1, $fechaAux->format('Y-m'));
            $fechaAux->addMonth();
            $col = $col + 2;
        }


        $fil = 2;
        foreach ($seguros as $seguro) {
            $sheet->setCellValue("A".$fil, $seguro->tercero->documento);
            $sheet->setCellValue("B".$fil, $seguro->tercero->nombre);
            $sheet->setCellValue("C".$fil, $seguro->estado);
            $sheet->setCellValue("D".$fil, $seguro->fecha);

            $fechaAux = clone $fechaInicial;
            $col = 6;
            for ($i=0; $i <= $diferenciaMeses; $i++) { 
                foreach ($seguro->facturas as $factura) {
                    $vencimiento = Carbon::parse($factura->vencimiento);
                    if($vencimiento->month == $fechaAux->month){
                        if($factura->cruzada == 1){
                            $sheet->setCellValueByColumnAndRow($col, $fil, $seguro->valor - 51 - 3300);
                            $sheet->setCellValueByColumnAndRow($col+1, $fil, 3300);
                        }else{
                            $sheet->setCellValueByColumnAndRow($col, $fil, 0);
                            $sheet->setCellValueByColumnAndRow($col+1, $fil, 0);
                        }
                        break;
                    }
                } 
                $col = $col + 2;
                $fechaAux->addMonth();             
            }
            $fil++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Prefactura.xlsx');
        
        return response()->download('Prefactura.xlsx', 'Prefactura.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function descargarCartera()
    {
        $hoy = Carbon::now();
        $finMes = Carbon::now()->lastOfMonth();
        $rango = [$hoy->format('Y-m') . '-01', $finMes->format('Y-m-d')];
        $facturas = Factura::with('tercero')->where('tipo', 'Venta')->doesnthave('credito')->whereNotNull('seguros_id')->whereBetween('vencimiento', $rango)->get();
        $creditosCobro = Credito::with('factura', 'cuotas', 'cliente')->where('estado', 'En cobro')->get();
        $creditosFinalizados = Credito::with('factura', 'cuotas', 'cliente')->where('estado', 'Finalizado')->whereHas('pagos', function($q) use($rango){$q->whereBetween('fecha', $rango);})->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]];
        $sheet->getStyle("A1:Z1")->applyFromArray($styleCentrar);

        $sheet->setCellValue("A1", "Identificación");
        $sheet->setCellValue("B1", "Nombre");
        $sheet->setCellValue("C1", "Placa");
        $sheet->setCellValue("D1", "Factura");
        $sheet->setCellValue("E1", "Fecha");
        $sheet->setCellValue("F1", "Vencimiento");
        $sheet->setCellValue("G1", "Mora");
        $sheet->setCellValue("H1", "Valor");
        $sheet->setCellValue("I1", "Cuota");
        $sheet->setCellValue("J1", "Saldo anterior");
        $sheet->setCellValue("K1", "Abonos");
        $sheet->setCellValue("L1", "Saldo ahora");
        $sheet->setCellValue("M1", "Destino");

        $fila = 2;
        foreach ($facturas as $factura) {
            $sheet->setCellValue("A".$fila, $factura->tercero->documento);
            $sheet->setCellValue("B".$fila, $factura->tercero->nombre);
            $sheet->setCellValue("C".$fila, "Seg. Vida");
            $sheet->setCellValue("D".$fila, $factura->prefijo . $factura->numero);
            $sheet->setCellValue("E".$fila, $factura->fecha);
            $sheet->setCellValue("F".$fila, $factura->vencimiento);
            if($factura->mora > 0){
                $sheet->setCellValue("G".$fila, $hoy->diffInDays($factura->vencimiento));
            }else{
                $sheet->setCellValue("G".$fila, 0);
            }
            $sheet->setCellValue("H".$fila, $factura->valor);
            $sheet->setCellValue("I".$fila, $factura->valor);
            $sheet->setCellValue("J".$fila, $factura->valor);
            if($factura->cruzada == "1"){
                $sheet->setCellValue("K".$fila, $factura->valor);
                $sheet->setCellValue("L".$fila, 0);
            }else{
                $sheet->setCellValue("K".$fila, 0);
                $sheet->setCellValue("L".$fila, $factura->valor);
            }
            $sheet->setCellValue("M".$fila, "Seguro Vida");
            $fila++;
        }

        foreach ($creditosCobro as $credito) {
            $sheet->setCellValue("A".$fila, $credito->cliente->nro_identificacion);
            $sheet->setCellValue("B".$fila, $credito->cliente->primer_nombre . " "  . $credito->cliente->primer_apellido);
            $sheet->setCellValue("C".$fila, $credito->placas);
            $sheet->setCellValue("D".$fila, $credito->factura->prefijo . $credito->factura->numero);
            $sheet->setCellValue("E".$fila, $credito->fecha);

            $seleccionada = null;
            $saldo = 0;
            foreach ($credito->cuotas as $cuota) {
                if($cuota->estado != "Pagada"){
                    if($seleccionada == null){
                        $seleccionada = $cuota->numero;
                        $sheet->setCellValue("F".$fila, $cuota->fecha_vencimiento);
                        $sheet->setCellValue("G".$fila, $cuota->mora);
                        $sheet->setCellValue("H".$fila, $credito->monto_total);
                        $sheet->setCellValue("I".$fila, $cuota->abono_capital + $cuota->interes);
                    }
                    $saldo = $saldo + $cuota->saldo_capital + $cuota->saldo_interes;
                }else{
                    $fechaCuota = Carbon::parse($cuota->fecha_vencimiento);
                    if($fechaCuota->format('Y-m') == $hoy->format('Y-m')){
                        if($seleccionada == null){
                            $seleccionada = $cuota->numero;
                            $sheet->setCellValue("F".$fila, $cuota->fecha_vencimiento);
                            $sheet->setCellValue("G".$fila, $cuota->mora);
                            $sheet->setCellValue("H".$fila, $credito->monto_total);
                            $sheet->setCellValue("I".$fila, $cuota->abono_capital + $cuota->interes);
                        }
                    }
                }
            }
            $pagos = Pago::where('creditos_id', $credito->id)->whereBetween('fecha', $rango)->sum('valor');
            $sheet->setCellValue("J".$fila, $saldo-$pagos);
            $sheet->setCellValue("K".$fila, $pagos);
            $sheet->setCellValue("L".$fila, $saldo);
            $sheet->setCellValue("M".$fila, $credito->tipo);
            $fila++;
        }

        foreach ($creditosFinalizados as $credito) {
            $sheet->setCellValue("A".$fila, $credito->cliente->nro_identificacion);
            $sheet->setCellValue("B".$fila, $credito->cliente->primer_nombre . " "  . $credito->cliente->primer_apellido);
            $sheet->setCellValue("C".$fila, $credito->placas);
            $sheet->setCellValue("D".$fila, $credito->factura->prefijo . $credito->factura->numero);
            $sheet->setCellValue("E".$fila, $credito->fecha);

            $saldo = 0;
            $ultima = count($credito->cuotas) - 1;

            $sheet->setCellValue("F".$fila, $credito->cuotas[$ultima]->fecha_vencimiento);
            $sheet->setCellValue("G".$fila, $credito->cuotas[$ultima]->mora);
            $sheet->setCellValue("H".$fila, $credito->monto_total);
            $sheet->setCellValue("I".$fila, $credito->cuotas[$ultima]->abono_capital + $credito->cuotas[$ultima]->interes);

            $pagos = Pago::where('creditos_id', $credito->id)->whereBetween('fecha', $rango)->sum('valor');
            $sheet->setCellValue("J".$fila, $saldo-$pagos);
            $sheet->setCellValue("K".$fila, $pagos);
            $sheet->setCellValue("L".$fila, $saldo);
            $sheet->setCellValue("M".$fila, $credito->tipo);
            $fila++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('CarteraInforme.xlsx');
        
        return response()->download('CarteraInforme.xlsx', 'Cartera' . ' ' . $hoy->format('Y-m') . '.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}