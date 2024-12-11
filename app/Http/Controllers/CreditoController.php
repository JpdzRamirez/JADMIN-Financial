<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Credito;
use App\Models\Credito_Costos;
use App\Models\Cuenta;
use App\Models\Evidencia;
use App\Models\Cuota;
use App\Models\Factura;
use App\Models\Movimiento;
use App\Models\Personal;
use App\Models\Referencia;
use App\Models\Resolucion;
use App\Models\Tasa;
use App\Models\Tipocredito;
use App\Models\TiposxCredito;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Luecano\NumeroALetras\NumeroALetras;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
use stdClass;
use Stenfrank\SoapDIAN\SOAPDIAN21;
use Stenfrank\UBL21dian\XAdES\SignInvoice;
use ZipArchive;

class CreditoController extends Controller
{
    public function listarSolicitudes()
    {
        $solicitudes = Credito::with('cliente')->where('estado', 'Solicitado')->orWhere('estado', 'Evaluando')->orderBy('id', 'desc')->get();

        return view('solicitudes.lista', compact('solicitudes'));
    }

    public function listarFinalizados()
    {
        $creditos = Credito::with('cliente')->where('estado', 'Finalizado')->orWhere('estado', 'Rechazado')->paginate(15);
        
        return view('creditos.finalizados', compact('creditos'));
    }

    public function listarCobro()
    {
        $creditos = Credito::with('cliente')->where('estado', 'En Cobro')->latest('id')->paginate(15);

        return view('creditos.cobro', compact('creditos'));
    }

    public function listarAprobados()
    {
        $creditos = Credito::with('cliente')->where('estado', 'Aprobado')->paginate(15);

        return view('creditos.aprobados', compact('creditos'));  
    }

    public function misCreditos()
    {
        $creditos = Credito::where('users_id', Auth::user()->id)->orderBy('id', 'desc')->get();

        return view('creditos.miscreditos', compact('creditos'));
    }

    public function nuevoCredito(Request $request)
    {       

        $costos = Costo::get();
        $tasa = Tasa::where('tipo', 'Interés')->orderBy('id', 'desc')->first();
        $tipos = Tipocredito::get();

        return view('creditos.nuevo', compact('costos', 'tasa', 'tipos'));
    }

    public function simularCredito(Request $request)
    {
        if ($request->input('rol') == "cahorsadm") {
            $tasa1 = ($request->input('tasa'))/100;
        }else{
            $tasa = Tasa::where('tipo', 'Interés')->orderBy('id', 'desc')->first();
            $tasa1 = $tasa->valor/100;        
        }
        $tasamv = pow(1+$tasa1, 1/12) - 1;
        
        $costos = Costo::whereIn('id', $request->input('costos'))->get();
        $monto = $request->input('total');

        foreach ($costos as $costo) {
            if($costo->tipo == "Absoluto"){
                if($costo->iva == 1){
                    $monto = $monto + $costo->valor*1.19;
                }else{
                    $monto = $monto + $costo->valor;
                }            
            }else{
                if($costo->iva == 1){
                    $monto = $monto +  ((($monto*$costo->valor)/100) * 1.19);
                }else{
                    $monto = $monto +  ( ($monto*$costo->valor)/100);
                }
            }
        }

        $plazo =  $request->input('plazo');
        $cuota = $monto * (($tasamv*pow(1+$tasamv, $plazo))/(pow(1+$tasamv, $plazo)-1));

        $respuesta = new stdClass();
        $respuesta->tmonto = number_format($monto, 0, ",", ".");
        $respuesta->plazo = $plazo;
        $respuesta->tasa = $request->input('tasa');
        $respuesta->cuota = number_format($cuota, 2, ",", ".");

        return json_encode($respuesta);
    }

    public function solicitarCredito(Request $request)
    {
        try {
            $cliente = User::with('referencias', 'personales')->where('nro_identificacion', $request->input('cliente'))->first();
            if($cliente != null){
                $costos = Costo::whereIn('id', $request->input('costos'))->get();       
                $monto = $request->input('total');
        
                $ivatotal = 0;
                $baseiva = 0;
                foreach ($costos as $costo) {
                    if($costo->tipo == "Absoluto"){
                        if($costo->iva == "1"){
                            $iva = $costo->valor*0.19;
                            $baseiva = $baseiva + $costo->valor;
                            $ivatotal = $ivatotal + $iva;
                            $monto = $monto + $costo->valor + $iva;
                        }else{
                            $monto = $monto + $costo->valor;
                        } 
                    }else{
                        if($costo->iva == "1"){
                            $porcentaje = ($request->input('total')*$costo->valor)/100;
                            $baseiva = $baseiva + $porcentaje;
                            $iva = $porcentaje*0.19;
                            $ivatotal = $ivatotal + $iva;
                            $monto = $monto + $porcentaje + $iva;
                        }else{
                            $monto = $monto +  (($request->input('total')*$costo->valor)/100);
                        }
                    }
                }

                if(count($cliente->referencias) == 0){
                    for ($i=0; $i < 3; $i++) {
                        $referencia = new Referencia();
                        if($i == 2){
                            $referencia->tipo = "Personal";                         
                        }else{
                            $referencia->tipo = "Familiar";
                            $referencia->parentesco = $request->input('refpar' . $i);
                        }                        
                        $referencia->nombre = $request->input('refnom' . $i);
                        $referencia->celular = $request->input('reftel' . $i);
                        $referencia->users_id = $cliente->id;
                        $referencia->save();
                    }
                }else{
                    for ($i=0; $i < 3; $i++) {
                        if($i == 2){
                            $cliente->referencias[$i]->tipo = "Personal";                         
                        }else{
                            $cliente->referencias[$i]->tipo = "Familiar";
                            $cliente->referencias[$i]->parentesco = $request->input('refpar' . $i);
                        }                        
                        $cliente->referencias[$i]->nombre = $request->input('refnom' . $i);
                        $cliente->referencias[$i]->celular = $request->input('reftel' . $i);
                        $cliente->referencias[$i]->users_id = $cliente->id;
                        $cliente->referencias[$i]->save();
                    }
                }  

                if($cliente->personales == null){
                    $personal = new Personal();
                    $personal->users_id = $cliente->id;  
                    $personal->save();
                    $cliente->personales = $personal;
                }
                
                $cliente->personales->hijos = $request->input('hijos');
                $cliente->personales->estado_civil = $request->input('civil');
                $cliente->personales->ocupacion = $request->input('ocupacion');
                $cliente->personales->tiempo_pareja = $request->input('pareja');
                $cliente->personales->vivienda = $request->input('vivienda');
                $cliente->personales->estrato = $request->input('estrato');
                $cliente->personales->tiempo_ocupacion = $request->input('ejerciendo');
                $cliente->personales->escolaridad = $request->input('escolaridad');
                $cliente->personales->ingresos = $request->input('ingresos');
                $cliente->personales->proveniencia = $request->input('proveniencia');
                $cliente->personales->save();

                $tasa = $request->input('tasa')/100;
                $tasamv = pow(1+$tasa, 1/12) - 1;
                $plazo =  $request->input('plazo');
                $cuota = $monto * (($tasamv*pow(1+$tasamv, $plazo))/(pow(1+$tasamv, $plazo)-1));
    
                $tiposCredito = Tipocredito::whereIn('id', $request->input('tipo'))->get();
                $credito = new Credito();
                $credito->monto = $request->input('total');
                $credito->iva = $ivatotal;
                $credito->baseiva = $baseiva;
                $credito->monto_total = $monto;
                $credito->pagadas = 0;
                $credito->plazo = $request->input('plazo');
                //$credito->tipo = $request->input('tipo');
                $credito->tipo = implode(',', $tiposCredito->pluck('nombre')->toArray());
                $credito->tasa = $request->input('tasa');
                $credito->fecha = Carbon::now();
                $credito->fecha_prestamo = $request->input('desembolso');
                $credito->pago = $cuota;
                $credito->estado = "Solicitado";
                $credito->users_id = $cliente->id;
                
                if($request->filled('placa')){
                    //$placa = Placa::find($request->input('placa'));
                    $credito->placas = $request->input('placa');
                }
                $credito->save(); 

                foreach ($costos as $costo) {
                    $creditoCosto = new Credito_Costos();
                    $creditoCosto->creditos_id = $credito->id;
                    $creditoCosto->costos_id = $costo->id;
                    if($costo->tipo == "Absoluto"){
                        $creditoCosto->valor = $costo->valor;
                    }else{
                        $creditoCosto->valor = ($request->input('total')*$costo->valor)/100;
                    }                 
                    $creditoCosto->save();
                }
                
                $montos = $request->input('monto');
                $tipos = $request->input('tipo');

                for ($i=0; $i < count($tipos); $i++) { 
                    $tipoxcredito = new TiposxCredito();
                    $tipoxcredito->creditos_id = $credito->id;
                    $tipoxcredito->tipos_credito_id = $tipos[$i];
                    $tipoxcredito->valor = $montos[$i];
                    $tipoxcredito->save();
                }
                
                return redirect('/solicitudes_credito');
    
            }else{
               return back()->withErrors(["sql" => "El cliente con identificación " . $request->input('cliente') . " no existe"]);
            }
        } catch (Exception $ex) {

            return back()->withErrors(["sql" => $ex->getMessage()]);
        }       
    }

    public function nuevoCreditoCliente()
    {
        $costos = Costo::get();
        $tasa = Tasa::where('tipo', 'Interés')->orderBy('id', 'desc')->first();
        $cliente = User::with('referencias', 'personales')->find(Auth::user()->id);
        $json = file_get_contents("https://crm.apptaxcenter.com/integracion/placas_propietario?key=97215612&identificacion=" . $cliente->nro_identificacion);
        $cliente->placas = json_decode($json);
        if($cliente->personales == null){
            $cliente->personales = new Personal();
        }
        $creditos = Credito::with('cuotas')->where('users_id', $cliente->id)->where('estado', 'Finalizado')->latest('id')->get();
        $mora = false;
        foreach ($creditos as $credito) {
            foreach ($credito->cuotas as $cuota) {
                if($cuota->mora > 5){
                    $mora = true;
                    break;
                }
            }
            if($mora){
                break;
            }
        }

        if(count($creditos) >= 3 && $mora == false){
            $cupo = 100000 + count($creditos) * 100000;
            if($cupo > 500000){
                $cupo = 500000;
            }
        }else{
            $cupo = 300000;
        }
            
        $condicion = Auth::user()->condicion;

        return view('creditos.cliente', compact('costos', 'tasa', 'cliente', 'condicion', 'cupo'));
    }

    public function solicitarCreditoCliente(Request $request)
    {
        try {
            $cliente = User::with('personales')->find(Auth::user()->id);
            $costos = Costo::whereIn('id', $request->input('costos'))->get();       
            $monto = $request->input('monto');
            $cliente->celular = $request->input('celular');
            $cliente->email = $request->input('email');
            $cliente->save();

            if($cliente->personales == null){
                $personal = new Personal();
                $personal->users_id = $cliente->id;  
                $personal->save();
                $cliente->personales = $personal;
            }
            
            $cliente->personales->hijos = $request->input('hijos');
            $cliente->personales->estado_civil = $request->input('civil');
            $cliente->personales->ocupacion = $request->input('ocupacion');
            $cliente->personales->tiempo_pareja = $request->input('pareja');
            $cliente->personales->vivienda = $request->input('vivienda');
            $cliente->personales->estrato = $request->input('estrato');
            $cliente->personales->tiempo_ocupacion = $request->input('ejerciendo');
            $cliente->personales->escolaridad = $request->input('escolaridad');
            $cliente->personales->ingresos = $request->input('ingresos');
            $cliente->personales->proveniencia = $request->input('proveniencia');
            $cliente->personales->save();
    
            $ivatotal = 0;
            $baseiva = 0;
            foreach ($costos as $costo) {
                if($costo->tipo == "Absoluto"){
                    if($costo->iva == "1"){
                        $iva = $costo->valor*0.19;
                        $baseiva = $baseiva + $costo->valor;
                        $ivatotal = $ivatotal + $iva;
                        $monto = $monto + $costo->valor + $iva;
                    }else{
                        $monto = $monto + $costo->valor;
                    } 
                }else{
                    if($costo->iva == "1"){
                        $porcentaje = ($request->input('monto')*$costo->valor)/100;
                        $baseiva = $baseiva + $porcentaje;
                        $iva = $porcentaje*0.19;
                        $ivatotal = $ivatotal + $iva;
                        $monto = $monto + $porcentaje + $iva;
                    }else{
                        $monto = $monto +  (($request->input('monto')*$costo->valor)/100);
                    }
                }
            }

            if($request->input('tipo') != "Seguro de Vida"){
                $referencias = Referencia::where('users_id', $cliente->id)->get();
                if(count($referencias) == 0){
                    for ($i=0; $i < 3; $i++) {
                        $referencia = new Referencia();
                        if($i == 2){
                            $referencia->tipo = "Personal";                         
                        }else{
                            $referencia->tipo = "Familiar";
                            $referencia->parentesco = $request->input('refpar' . $i);
                        }                        
                        $referencia->nombre = $request->input('refnom' . $i);
                        $referencia->celular = $request->input('reftel' . $i);
                        $referencia->users_id = $cliente->id;
                        $referencia->save();
                    }
                }else{
                    for ($i=0; $i < 3; $i++) {
                        if($i == 2){
                            $referencias[$i]->tipo = "Personal";                         
                        }else{
                            $referencias[$i]->tipo = "Familiar";
                            $referencias[$i]->parentesco = $request->input('refpar' . $i);
                        }                        
                        $referencias[$i]->nombre = $request->input('refnom' . $i);
                        $referencias[$i]->celular = $request->input('reftel' . $i);
                        $referencias[$i]->users_id = $cliente->id;
                        $referencias[$i]->save();
                    }
                } 
            }
            

            $tasa = $request->input('tasa')/100;
            $tasamv = pow(1+$tasa, 1/12) - 1;
            $plazo =  $request->input('plazo');
            $cuota = $monto * (($tasamv*pow(1+$tasamv, $plazo))/(pow(1+$tasamv, $plazo)-1));

            $tipoCredito = Tipocredito::find($request->input('tipo'));
            $credito = new Credito();
            $credito->monto = $request->input('monto');
            $credito->iva = $ivatotal;
            $credito->baseiva = $baseiva;
            $credito->monto_total = $monto;
            $credito->pagadas = 0;
            $credito->plazo = $request->input('plazo');
            $credito->tipo = $tipoCredito->nombre;
            $credito->tasa = $request->input('tasa');
            $credito->fecha = Carbon::now();
            $credito->pago = $cuota;
            $credito->estado = "Solicitado";
            $credito->aplicacion = 1;
            $credito->users_id = $cliente->id;

            if($request->filled('placa')){
                //$placa = Placa::find($request->input('placa'));
                $credito->placas = $request->input('placa');
            }
            $credito->save();

            foreach ($costos as $costo) {
                $creditoCosto = new Credito_Costos();
                $creditoCosto->creditos_id = $credito->id;
                $creditoCosto->costos_id = $costo->id;
                if($costo->tipo == "Absoluto"){
                    $creditoCosto->valor = $costo->valor;
                }else{
                    $creditoCosto->valor = ($request->input('monto')*$costo->valor)/100;
                }                 
                $creditoCosto->save();
            }  
            
            $tipoxcredito = new TiposxCredito();
            $tipoxcredito->creditos_id = $credito->id;
            $tipoxcredito->tipos_credito_id = $tipoCredito->id;
            $tipoxcredito->valor = $credito->monto;
            $tipoxcredito->save();

            return redirect('/mis_creditos');

        } catch (Exception $ex) {
            return back()->withErrors(["sql" => $ex->getMessage()]);
        }       
    }

    public function evaluarCredito($solicitud)
    {
       $credito = Credito::with(['cliente'=>function($q){$q->with('referencias', 'personales');}], 'placa')->find($solicitud);
       if($credito->estado == "Solicitado"){
            $credito->estado = "Evaluando";
            $credito->save();
       }
       /*$credito->finalizados = Credito::where('users_id', $credito->users_id)->where('estado', 'Finalizado')->count();
       $credito->cobro = Credito::where('users_id', $credito->users_id)->where('estado', 'En cobro')->count();
       $credito->proceso = Credito::where('users_id', $credito->users_id)->where('estado', 'Proceso')->count();*/
       $vinculacion = null;
       $hoy = Carbon::now();
       if($credito->cliente->condicion == "Conductor"){
            //$json = file_get_contents("http://localhost/integracion/cliente_conductor?key=97215612&cedula=" . $credito->cliente->nro_identificacion);
            $json = file_get_contents("https://crm.apptaxcenter.com/integracion/cliente_conductor?key=97215612&cedula=" . $credito->cliente->nro_identificacion);
            $vinculacion = json_decode($json);
            $vinculacion->tipo = "Conductor";
            if($vinculacion->fecha != null){
                $fechaVinculacion = Carbon::parse($vinculacion->fecha);
                $vinculacion->tiempo = $hoy->diffInDays($fechaVinculacion);
            }
        }elseif($credito->cliente->condicion == "Propietario"){
            //$json = file_get_contents("http://localhost/integracion/cliente_propietario?key=97215612&cedula=" . $credito->cliente->nro_identificacion);
            $json = file_get_contents("https://crm.apptaxcenter.com/integracion/cliente_propietario?key=97215612&cedula=" . $credito->cliente->nro_identificacion);
            $vinculacion = json_decode($json);
            $vinculacion->tipo = "Propietario";
            $mayor = 0;
            foreach ($vinculacion->placas as $placa) {
                $fechaVinculacion = Carbon::parse($placa->fecha);
                $diferencia = $hoy->diffInDays($fechaVinculacion);
                if($diferencia > $mayor){
                    $mayor = $diferencia;
                }
            }
            $vinculacion->tiempo = $mayor;
        }
        $credito->score = intval($this->calcularScore($credito->cliente->personales, $credito->cliente->id));
       
       return view('creditos.evaluar', compact('credito', 'vinculacion'));
    }

    public function evaluacionCredito(Request $request, $solicitud)
    {
        $credito = Credito::with(['costos'=>function($q){$q->with('cuenta');}])->find($solicitud);
        if($credito->estado == "Evaluando"){
            if($request->input('decision') == "1"){
                $ultimo = Credito::latest('numero')->first();
                $credito->estado = "Aprobado";
                $credito->numero = $ultimo->numero + 1;
                $credito->score = $request->input('score');   
                $credito->fecha_resultado = Carbon::now();
                $credito->save();
    
                if($request->hasFile('evidencias')){
                    $archivos = $request->file('evidencias');
                    foreach ($archivos as $archivo) {
                        $evidencia = new Evidencia();
                        $evidencia->nombre = $archivo->getClientOriginalName();
                        $evidencia->archivo = base64_encode($archivo->getContent());
                        $evidencia->creditos_id = $credito->id;
                        $evidencia->save();
                    }
                }
    
                return redirect('/creditos_aprobados');
            }else{
                $credito->estado = "Rechazado";
                $credito->save();
    
                return redirect('/solicitudes_credito');
            } 
        }else{
            return abort("404", "Crédito ya evaluado");
        }       
    }

    public function colocarCredito($credito)
    {
        $credito = Credito::with('cliente', 'placa')->find($credito);
        return view('creditos.colocacion', compact('credito'));
        if($credito->estado == "Aprobado"){
            return view('creditos.colocacion', compact('credito'));
        }else{
            abort(404);
        }
    }

    public function bajarFormulario($credito)
    {
        $credito = Credito::with('cliente', 'costos')->find($credito);

        $spreadsheet = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "cahors.xlsx");
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue("B5", $credito->fecha);
        $sheet->setCellValue("L5", $credito->numero);
        $sheet->setCellValueExplicit("D10", $credito->cliente->nro_identificacion, DataType::TYPE_STRING);      
        $sheet->setCellValue("D12", $credito->cliente->primer_apellido);
        $sheet->setCellValue("J12", $credito->cliente->segundo_apellido);
        $sheet->setCellValue("D14", $credito->cliente->primer_nombre . " " . $credito->cliente->segundo_nombre);
        
        $sheet->setCellValue("D18", $credito->cliente->direccion);
        $sheet->setCellValue("D21", $credito->cliente->barrio);
        $sheet->setCellValueExplicit("J21", $credito->cliente->celular, DataType::TYPE_STRING);
        $sheet->setCellValue("D23", $credito->cliente->municipio);
        $sheet->setCellValue("J23", $credito->cliente->email);
        if ($credito->placas != null) {
            $sheet->setCellValue("J29", "TAXSUR");
            $sheet->setCellValue("D31", $credito->placas);
        }
        $sheet->setCellValue("J31", $credito->cliente->condicion);
        if(count($credito->cliente->referencias) > 0){
            $sheet->setCellValue("D65", $credito->cliente->referencias[0]->nombre);
            $sheet->setCellValueExplicit("J65", $credito->cliente->referencias[0]->celular, DataType::TYPE_STRING);
            $sheet->setCellValue("D67", $credito->cliente->referencias[0]->parentesco);
            $sheet->setCellValue("D70", $credito->cliente->referencias[1]->nombre);
            $sheet->setCellValueExplicit("J70", $credito->cliente->referencias[1]->celular, DataType::TYPE_STRING);
            $sheet->setCellValue("D72", $credito->cliente->referencias[1]->parentesco);
            $sheet->setCellValue("D58", $credito->cliente->referencias[2]->nombre);
            $sheet->setCellValueExplicit("J58", $credito->cliente->referencias[2]->celular, DataType::TYPE_STRING);
        }
        $sheet->setCellValue("D78", $credito->monto);
        $sheet->setCellValueExplicit("D80", $credito->plazo, DataType::TYPE_STRING);
        $sheet->setCellValue("J80", $credito->tipo);

        $spreadsheet->setActiveSheetIndex(1);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue("E14", $credito->tasa/100);
        $sheet->setCellValue("E16", $credito->monto);
        $sheet->setCellValue("E17", $credito->plazo);

        foreach ($credito->costos as $costo) {
            if($costo->descripcion == "Soporte"){
                $sheet->setCellValue("E18", $costo->valor);
            }elseif ($costo->descripcion == "Seguro") {
                $sheet->setCellValue("E19", $costo->valor);
            }elseif ($costo->descripcion == "Inclusión financiera") {
                $sheet->setCellValue("E20", "=(E16*" . $costo->valor . ")/100");
            }elseif ($costo->descripcion == "Comisión") {
                $sheet->setCellValue("E21", "=(E16*" . $costo->valor . ")/100");
            }elseif ($costo->descripcion == "Procesamiento de datos Plataforma") {
                $sheet->setCellValue("E22", $costo->valor);
            }elseif ($costo->descripcion == "Soporte 2") {
                $sheet->setCellValue("E23", $costo->valor);
            }elseif ($costo->descripcion == "Consultas Centrales") {
                $sheet->setCellValue("E24", $costo->valor);
            }
        }
        if($credito->fecha_prestamo != null){
            $sheet = $spreadsheet->setActiveSheetIndex(2);
            $sheet->setCellValue("D16", $credito->fecha_prestamo);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "formulario.xlsx");

        $datos = file_get_contents(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "formulario.xlsx");
        $evidencia = Evidencia::where('creditos_id', $credito->id)->where('nombre', 'Formulario')->first();
        if($evidencia == null){
            $evidencia = new Evidencia();
        }
        $evidencia->nombre = "Formulario";
        $evidencia->archivo = base64_encode($datos);
        $evidencia->creditos_id = $credito->id;
        $evidencia->save();

        return $evidencia->archivo;
    }

    public function colocacionCredito(Request $request)
    {
        set_time_limit(0);
        $credito = Credito::with(['costos'=>function($q){$q->with('cuenta');}, 'cliente.tercero', 'tipos'])->find($request->input('credito'));
        $hoy = Carbon::now();

        $ultimaFact = Factura::where('prefijo', 'FECR')->where('tipo', 'Venta')->orderBy('numero', 'desc')->first();
        $factura = new Factura();
        $factura->prefijo = "FECR";
        $factura->numero = $ultimaFact->numero + 1;
        $factura->fecha = Carbon::now();
        $factura->valor = $credito->monto_total;
        $factura->tipo = "Venta";
        $factura->descripcion = "Factura de venta #" . $factura->prefijo.$factura->numero;
        $factura->formapago = "Crédito";
        $factura->creditos_id = $credito->id;
        $factura->terceros_id = $credito->cliente->tercero->id;
        if($credito->fecha_prestamo == null){
            $credito->fecha_prestamo = $hoy;
        }
        
        $resolucion = Resolucion::where('prefijo', $factura->prefijo)->first();
        $concatFact = $factura->prefijo . $factura->numero;
        $segCode = hash("sha384", '7298e08a-57de-4c43-83a3-573a0992880912345' . $concatFact);
        $cufe = hash("sha384", $concatFact . $hoy->format('Y-m-d') . $hoy->format('H:i:s') . '-05:00' . 
        number_format($credito->monto_total-$credito->iva, 2, ".", "") . '01' . 
        number_format($credito->iva, 2, ".", "") . '040.00030.00' . number_format($credito->monto_total, 2, ".", "") . '901318591' . 
        $credito->cliente->nro_identificacion . $resolucion->citec . '1');
        $finMes = Carbon::now()->lastOfMonth();
        $vencimiento = Carbon::parse($factura->fecha_prestamo)->addMonth();
        $qrcode = 'NroFactura=' . $concatFact . PHP_EOL .
        'NitFacturador=901318591' . PHP_EOL .
        'NitAdquiriente=' . $credito->cliente->nro_identificacion . PHP_EOL .
        'FechaFactura=' . $hoy->format("Y-m-d") . PHP_EOL .
        'ValorTotalFactura=' . $credito->monto_total . PHP_EOL .
        'CUFE=' . $cufe . PHP_EOL .
        'URL=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $cufe;
        $xmlView = view('facturas.ublCredito', compact('credito', 'hoy', 'vencimiento', 'finMes', 'factura', 'cufe', 'segCode', 'qrcode', 'resolucion'))->render();
        
        $storePath = storage_path();
        if(!is_dir($storePath . "/facturas/FECR/" . $concatFact)){
            mkdir($storePath . "/facturas/FECR/" . $concatFact);
        }
        $carpeta = $storePath . "/facturas/FECR/" . $concatFact . "/";
        $xmlView = '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL .$xmlView;
        $pathCertificate = $storePath . "/claves/Certificado.pfx";
        $passwors = '3ZDVyH24R3';
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlView);
        $signInvoice = new SignInvoice($pathCertificate, $passwors, $xmlView);

        Storage::disk('facturas')->put("/FECR/" . $concatFact . "/" . $concatFact . ".xml", $signInvoice->xml);
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
        $xml = $soapdian21->soap;

        $headers = array(
            "Content-type: application/soap+xml;charset=\"utf-8\"",
            "SOAPAction: http://wcf.dian.colombia/IWcfDianCustomerServices/SendBillSync", 
            "Content-length: ".strlen($xml),
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch); 
        Storage::disk('facturas')->put("/FECR/" . $concatFact . "/" . $concatFact . "Response.xml", $response);
        $pos = strpos($response, "<b:StatusCode>");
        curl_close($ch);
        //if(true){
        if(substr($response, $pos, 16) == "<b:StatusCode>00" || $factura->numero == '1777'){
            $factura->cufe = $cufe;
            $factura->save();
            if($request->hasFile('pagare')){
                $pagare = $request->file('pagare');
                $evidencia = new Evidencia();
                $evidencia->nombre = "Pagaré";
                $evidencia->archivo = base64_encode($pagare->getContent());
                $evidencia->creditos_id = $credito->id;
                $evidencia->save();
            }
            $credito->estado = "En cobro";
            $credito->save();
    
            $vencimientoCuotas = Carbon::parse($credito->fecha_prestamo);
            $saldo = $credito->monto_total;
            $mv = pow(1 + ($credito->tasa/100), 30/360) - 1;
            $fechaHoy = Carbon::now()->setTime(0,0);
            for ($i=1; $i <= $credito->plazo; $i++) { 
                $cuota = new Cuota();
                $cuota->ncuota = $i;
                $cuota->saldo_insoluto = $saldo;
                $cuota->interes = $mv*$saldo;
                $cuota->abono_capital = $credito->pago - $cuota->interes;
               
                if($i == 1 && $vencimientoCuotas <= $fechaHoy){
                    $cuota->estado = "Vigente";
                }else{
                    $cuota->estado = "Pendiente";
                }
                $cuota->fecha_vencimiento = $vencimientoCuotas->addMonth();
                $cuota->saldo_capital = $cuota->abono_capital;
                $cuota->saldo_interes = $cuota->interes;
                $cuota->saldo_mora = 0;
                $cuota->fecha_mora = $cuota->fecha_vencimiento;
                $cuota->creditos_id = $credito->id;
                $cuota->mora = 0;
                $cuota->save();
    
                $saldo = $saldo - $cuota->abono_capital;     
            }
    
            $iva = 0;
            foreach ($credito->costos as $costo) {
                $costo->cuenta->total = $costo->cuenta->total + $costo->valor;
                $costo->cuenta->save();
                if($costo->iva == "1"){
                    $iva = $iva + $costo->pivot->valor*0.19;
                }
    
                $movimiento = new Movimiento();
                $movimiento->fecha = $factura->fecha;
                $movimiento->naturaleza = "Crédito";
                $movimiento->valor = $costo->valor;
                $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .  $costo->cuenta->nombre;
                $movimiento->cuentas_id = $costo->cuenta->id;
                $movimiento->facturas_id = $factura->id;
                $movimiento->terceros_id = $credito->cliente->terceros_id;
                $movimiento->save();
            }
    
            $civa = Cuenta::find(170);
            if($civa != null){
                $civa->total = $civa->total + $iva;
                $civa->save();
    
                $movimiento = new Movimiento();
                $movimiento->fecha = $factura->fecha;
                $movimiento->naturaleza = "Crédito";
                $movimiento->valor = $iva;
                $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .  $civa->nombre;
                $movimiento->cuentas_id = $civa->id;
                $movimiento->facturas_id = $factura->id;
                $movimiento->terceros_id = $credito->cliente->terceros_id;
                $movimiento->save();
            }

            $cfinan = Cuenta::find(213);
            $libreInversion = 0;
            if($cfinan != null){
                if(count($credito->tipos) > 0){
                    foreach ($credito->tipos as $tipo) {
                        $movimiento = new Movimiento();
                        $movimiento->fecha = $factura->fecha;
                        $movimiento->naturaleza = "Crédito";
                        $movimiento->valor = $tipo->pivot->valor;
                        $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .  $cfinan->nombre;
                        if($tipo->id <= 2){  
                            $movimiento->cuentas_id = $cfinan->id;
                        }else{
                            $libreInversion = 233;
                            $movimiento->cuentas_id = 234;
                            $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " DESEMBOLSO CREDITOS LIBRE INVERSION";
                        }                
                        $movimiento->facturas_id = $factura->id;
                        if($tipo->id == 1){
                            $movimiento->terceros_id = 314;
                        }else{
                            $movimiento->terceros_id = $credito->cliente->terceros_id;
                        }
                        $movimiento->save();
                    }
                }else{
                    $movimiento = new Movimiento();
                    $movimiento->fecha = $factura->fecha;
                    $movimiento->naturaleza = "Crédito";
                    $movimiento->valor = $credito->monto;
                    $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .  $cfinan->nombre;
                    $movimiento->cuentas_id = $cfinan->id;
                    $movimiento->facturas_id = $factura->id;
                    $movimiento->terceros_id = $credito->cliente->terceros_id;
                    $movimiento->save();
                    $terceroDebito = $credito->cliente->terceros_id;
                }
            }
            
            if($libreInversion == 0){
                $cclientes = Cuenta::find(212);
            }else{
                $cclientes = Cuenta::find($libreInversion);
            }
        
            if($cclientes != null){
                $movimiento = new Movimiento();
                $movimiento->fecha = $factura->fecha;
                $movimiento->naturaleza = "Débito";
                $movimiento->valor = $credito->monto_total;
                $movimiento->concepto = $factura->prefijo . " " . $factura->numero . " " .  $cclientes->nombre;
                $movimiento->cuentas_id = $cclientes->id;
                $movimiento->facturas_id = $factura->id;
                $movimiento->terceros_id = $credito->cliente->terceros_id;
                $movimiento->save();
            }

            $qrGen = new BaconQrCodeGenerator();
            $imgqr = base64_encode($qrGen->size(100)->format('png')->generate($qrcode));
            $vencimientoMes = Carbon::parse($factura->fecha_prestamo)->addMonth();
            $formater = new NumeroALetras();
            $letras = $formater->toWords($credito->monto_total, 2);
            $hoy = Carbon::now();
            PDF::loadView('notificaciones.facturaVenta', compact('factura', 'hoy', 'imgqr', 'vencimientoMes', 'letras', 'cufe', 'resolucion'))->save($carpeta . $concatFact . ".pdf");

            $zip = new ZipArchive();
            $zip->open($carpeta . $concatFact . "Email.zip", ZipArchive::CREATE);
            $zip->addFile($carpeta . $concatFact . ".xml", $concatFact . ".xml");
            $zip->addFile($carpeta . $concatFact . ".pdf", $concatFact . ".pdf");
            $zip->close();

            $to = $credito->cliente->email;
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
            }

            return redirect('/creditos/' . $credito->id . '/plan_pagos')->with('factura', $factura->id);
        }else{
            return "Falló envio de factura";
        }
    }

    public function planPagos($credito)
    {
        $credito = Credito::with('cuotas', 'factura.tercero')->find($credito);

        return view('clientes.pagos', compact('credito'));
    }

    public function descargarFactura($credito)
    {
        $credito = Credito::with(['costos'=>function($q){$q->with('cuenta');}, 'cliente', 'placa'])->find($credito);
        $formater = new NumeroALetras();
        $letras = $formater->toWords($credito->monto_total, 2);
        $hora = Carbon::now();
        $dompdf = PDF::loadView('facturas.nuevoCredito', compact('credito', 'letras', 'hora'));
        
        return $dompdf->stream("Factura Credito #" . $credito->id . ".pdf");
    }

    public function getSolicitudes()
    {
        $solicitudes = Credito::select('id')->where('estado', 'Solicitado')->get();

        return json_encode($solicitudes);
    }

    public function calcularScore($personales, $idCliente)
    {
        $score = 0;
        if($personales->tiempo_ocupacion >= 0 && $personales->tiempo_ocupacion <= 5){
            $puntaje = 300;
        }elseif ($personales->tiempo_ocupacion > 5 && $personales->tiempo_ocupacion <= 10) {
            $puntaje = 600;
        }elseif ($personales->tiempo_ocupacion > 10 && $personales->tiempo_ocupacion <= 15) {
            $puntaje = 750;
        }else{
            $puntaje = 900;
        }
        $score = $score + ($puntaje*0.1);

        if($personales->ingresos >= 0 && $personales->ingresos <= 1000000){
            $puntaje = 200;
        }elseif ($personales->ingresos > 1000000 && $personales->ingresos <= 2000000) {
            $puntaje = 450;
        }elseif ($personales->ingresos > 2000000 && $personales->ingresos <= 3000000) {
            $puntaje = 600;
        }elseif ($personales->ingresos > 3000000 && $personales->ingresos <= 4000000) {
            $puntaje = 750;
        }else{
            $puntaje = 900;
        }
        $score = $score + ($puntaje*0.15);

        if($personales->estado_civil == "Soltero"){
            $puntaje = 600;
        }elseif ($personales->estado_civil == "Casado") {
            $puntaje = 800;
        }elseif ($personales->estado_civil == "Viudo") {
            $puntaje = 600;
        }elseif ($personales->estado_civil == "Divorciado") {
            $puntaje = 400;
        }else{
            $puntaje = 600;
        }
        $score = $score + ($puntaje*0.08);

        if($personales->tiempo_pareja >= 0 && $personales->tiempo_pareja <= 1){
            $puntaje = 400;
        }elseif ($personales->tiempo_pareja > 1 && $personales->tiempo_pareja <= 5) {
            $puntaje = 600;
        }elseif ($personales->tiempo_pareja > 5 && $personales->tiempo_pareja <= 10) {
            $puntaje = 800;
        }else{
            $puntaje = 900;
        }
        $score = $score + ($puntaje*0.08);

        if($personales->hijos == 0){
            $puntaje = 600;
        }elseif ($personales->hijos == 1) {
            $puntaje = 700;
        }elseif ($personales->hijos == 2) {
            $puntaje = 550;
        }else{
            $puntaje = 450;
        }
        $score = $score + ($puntaje*0.1);

        if($personales->vivienda == "Propia (Totalmente pagada)"){
            $puntaje = 850;
        }elseif ($personales->vivienda == "Propia (Crédito hipotecario vigente)") {
            $puntaje = 750;
        }elseif ($personales->vivienda == "Familiar") {
            $puntaje = 600;
        }elseif ($personales->vivienda == "Arriendo") {
            $puntaje = 600;
        }
        $score = $score + ($puntaje*0.11);

        if($personales->estrato == 1){
            $puntaje = 500;
        }elseif ($personales->estrato >= 2 && $personales->estrato <= 3) {
            $puntaje = 600;
        }elseif ($personales->estrato >= 4) {
            $puntaje = 800;
        }
        $score = $score + ($puntaje*0.1);

        if($personales->escolaridad == "Primaria"){
            $puntaje = 500;
        }elseif ($personales->escolaridad == "Secundaria") {
            $puntaje = 750;
        }elseif ($personales->escolaridad == "Técnica o tecnológica") {
            $puntaje = 800;
        }elseif ($personales->escolaridad == "Pregrado") {
            $puntaje = 850;
        }elseif ($personales->escolaridad == "Postgrado") {
            $puntaje = 950;
        }elseif ($personales->escolaridad == "Ninguno") {
            $puntaje = 400;
        }
        $score = $score + ($puntaje*0.08);

        $ultimo = Credito::with('cuotas')->where('users_id', $idCliente)->latest('id')->first();
        if($ultimo != null){
            $mora = 0;
            for ($i = count($ultimo->cuotas)-1 ; $i >= 0; $i--) { 
                if($ultimo->cuotas[$i]->mora > 0){
                    $mora = $ultimo->cuotas[$i]->mora;
                    break;
                }
            }

            if($mora == 0){
                $puntaje = 900;
            }elseif($mora >= 1 && $mora <= 30){
                $puntaje = 600;
            }elseif ($mora > 30 && $mora <= 60) {
                $puntaje = 500;
            }elseif ($mora > 60 && $mora <= 90) {
                $puntaje = 350;
            }else{
                $puntaje = 150;
            }
            $score = $score + ($puntaje*0.2);
        }else{
            $score = $score + (450*0.2);
        }

        return $score;
    }
}
