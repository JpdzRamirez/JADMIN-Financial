<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Cuenta;
use App\Models\Factura;
use App\Models\FacturasxRecibo;
use App\Models\Movimiento;
use App\Models\Nota;
use App\Models\TerceroCahors;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InformeController extends Controller
{
    public function formLibroAuxiliar()
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $fecha = Carbon::now();

        return view('informes.libroAuxiliar', compact('fecha'));
    }

    public function getLibroAuxiliar(Request $request)
    {
        try {
            ini_set('memory_limit', -1);
            set_time_limit(0);
            $fechain = $request->input('fechain') . ' 00:00';
            $fechafi = $request->input('fechafi') . ' 23:59';
            if($request->filled('cuentain')){
                $cuentain = $request->input('cuentain');
            }else{
                $cuentain = '0';
            }
            if($request->filled('cuentafi')){
                $cuentafi = $request->input('cuentafi');
            }else{
                $cuentafi = '6';
            }

            $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
            if($tercero != null){
                $idter = $tercero->id;
                $cuentas = Cuenta::with(['movimientos'=>function($q) use($fechain, $fechafi, $idter){
                    $q->with('tercero')->where('terceros_id', $idter)->where('estado', 1)->whereBetween('fecha', [$fechain, $fechafi])->orderBy('fecha', 'asc');}])->whereBetween('codigo', [$cuentain, $cuentafi])->orderBy('codigo', 'asc')->get();
            }else{
                $cuentas = Cuenta::with(['movimientos'=>function($q) use($fechain, $fechafi){
                    $q->with('tercero')->where('estado', 1)->whereBetween('fecha', [$fechain, $fechafi])->orderBy('fecha', 'asc');}])->whereBetween('codigo', [$cuentain, $cuentafi])->orderBy('codigo', 'asc')->get();    
            }

            $libro = 1;
            $fecha = Carbon::now();

            $dompdf = PDF::loadView('informes.pdfLibro', compact('cuentas', 'cuentain', 'cuentafi', 'fechain', 'fechafi', 'fecha', 'libro', 'tercero'));
            return  $dompdf->stream("Libro.pdf");
        } catch (Exception $ex) {
            return $ex->getMessage();
        } 
    }

    public function descargarLibroAuxiliar(Request $request)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $fechain = $request->input('fechain') . ' 00:00';
        $fechafi = $request->input('fechafi') . ' 23:59';
        if($request->filled('cuentain')){
            $cuentain = $request->input('cuentain');
        }else{
            $cuentain = '0';
        }
        if($request->filled('cuentafi')){
            $cuentafi = $request->input('cuentafi');
        }else{
            $cuentafi = '6';
        }

        $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
        if($tercero != null){
            $idter = $tercero->id;
            $cuentas = Cuenta::with(['movimientos'=>function($q) use($fechain, $fechafi, $idter){
                $q->with('tercero')->where('terceros_id', $idter)->where('estado', 1)->whereBetween('fecha', [$fechain, $fechafi])->orderBy('fecha', 'asc');}])->whereBetween('codigo', [$cuentain, $cuentafi])->orderBy('codigo', 'asc')->get();
        }else{
            $cuentas = Cuenta::with(['movimientos'=>function($q) use($fechain, $fechafi){
                $q->with('tercero')->where('estado', 1)->whereBetween('fecha', [$fechain, $fechafi])->orderBy('fecha', 'asc');}])->whereBetween('codigo', [$cuentain, $cuentafi])->orderBy('codigo', 'asc')->get();    
        }
        
        $fecha = Carbon::now();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font'=>['bold'=>true]];

        $sheet->setCellValue("A1", "LIBRO AUXILIAR");
        $sheet->mergeCells("A1:F1");
        $sheet->setCellValue("A2", "Desde " . $cuentain . " hasta " . $cuentafi);
        $sheet->mergeCells("A2:F2");
        $sheet->setCellValue("A3", "Desde " . $fechain . " hasta " . $fechafi);
        $sheet->mergeCells("A3:F3");
        if($tercero != null){
            $sheet->setCellValue("A4", "Tercero:  " . $tercero->documento . ", " . $tercero->nombre);
            $sheet->mergeCells("A4:F4");
            $sheet->getStyle("A1:F4")->applyFromArray($styleCentrar);
            $i = 6;
        }else{
            $sheet->mergeCells("A3:F3");
            $sheet->getStyle("A1:F3")->applyFromArray($styleCentrar);
            $i = 5;
        }
        
        foreach ($cuentas as $cuenta) {
            if($tercero != null){
                $parcialDebito = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Débito')->whereDate('fecha', '<', $fechain)->where('terceros_id', $tercero->id)->where('estado', 1)->sum('valor');
                $parcialCredito = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Crédito')->whereDate('fecha', '<', $fechain)->where('terceros_id', $tercero->id)->where('estado', 1)->sum('valor');
            }else{
                $parcialDebito = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Débito')->whereDate('fecha', '<', $fechain)->where('estado', 1)->sum('valor');
                $parcialCredito = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Crédito')->whereDate('fecha', '<', $fechain)->where('estado', 1)->sum('valor');
            }
            
            $saldo = $parcialDebito - $parcialCredito;

            if($saldo != 0 || count($cuenta->movimientos) > 0){
                $sheet->setCellValue("A". $i, $cuenta->codigo . "-" . $cuenta->nombre);
                $sheet->mergeCells("A" . $i . ":F" . $i);
                $sheet->getStyle("A" . $i . ":F" . $i)->applyFromArray($styleCentrar);
                $i= $i + 2;
    
                $sheet->setCellValue("A" . $i, "Fecha");
                $sheet->setCellValue("B" . $i, "Observación");
                $sheet->setCellValue("C" . $i, "Tercero");      
                $sheet->setCellValue("D" . $i, "Débito");
                $sheet->setCellValue("E" . $i, "Crédito");
                $sheet->setCellValue("F" . $i, "Saldo");
                $sheet->getStyle("A" . $i . ":F" . $i)->applyFromArray($styleCentrar);
                $i++;   
    
                $sheet->setCellValue("A" . $i, "");
                $sheet->setCellValue("B" . $i, "");
                $sheet->setCellValue("C" . $i, "Parciales");      
                $sheet->setCellValue("D" . $i, $parcialDebito);
                $sheet->setCellValue("E" . $i, $parcialCredito);
                $sheet->setCellValue("F" . $i, $saldo);
                $sheet->getStyle("C" . $i . ":F" . $i)->applyFromArray($styleCentrar);
                $i++;
    
                $debitos = 0;
                $creditos = 0;
                $saldo = $saldo;
    
                foreach ($cuenta->movimientos as $movimiento) {
                    $sheet->setCellValue("A".$i, $movimiento->fecha);
                    $sheet->setCellValue("B".$i, $movimiento->concepto);
                    $sheet->setCellValue("C".$i, $movimiento->tercero->documento . " - " . $movimiento->tercero->nombre);;
                    if ($movimiento->naturaleza == "Débito") {
                        $sheet->setCellValue("D".$i, $movimiento->valor);
                        $sheet->setCellValue("E".$i, 0);
                        $sheet->setCellValue("F".$i, $saldo + $movimiento->valor);
                        $debitos = $debitos + $movimiento->valor;
                        $saldo = $saldo + $movimiento->valor;
                    }else{
                        $sheet->setCellValue("D".$i, 0);
                        $sheet->setCellValue("E".$i, $movimiento->valor);
                        $sheet->setCellValue("F".$i, $saldo - $movimiento->valor);
                        $creditos = $creditos + $movimiento->valor;
                        $saldo = $saldo - $movimiento->valor;
                    }       
                    $i++;
                }
                $sheet->setCellValue("A" . $i, "");
                $sheet->setCellValue("B" . $i, "");
                $sheet->setCellValue("C" . $i, "Parciales");      
                $sheet->setCellValue("D" . $i, $debitos);
                $sheet->setCellValue("E" . $i, $creditos);
                $sheet->setCellValue("F" . $i, $saldo);
                $sheet->getStyle("C" . $i . ":F" . $i)->applyFromArray($styleCentrar);
                $i++;
                $sheet->setCellValue("A" . $i, "");
                $sheet->setCellValue("B" . $i, "");
                $sheet->setCellValue("C" . $i, "Totales");      
                $sheet->setCellValue("D" . $i, $parcialDebito + $debitos);
                $sheet->setCellValue("E" . $i, $parcialCredito + $creditos);
                $sheet->setCellValue("F" . $i, $saldo);
                $sheet->getStyle("C" . $i . ":F" . $i)->applyFromArray($styleCentrar);
                $i = $i + 3;
            }     
        }
        
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Libro.xlsx');
        $archivo = file_get_contents('Libro.xlsx');
        unlink('Libro.xlsx');

        return base64_encode($archivo);

    }

    public function formCarteraGenerica()
    {
        $fecha = Carbon::now();

        return view('informes.carteraGenerica', compact('fecha'));
    }

    public function descargarCarteraGenerica(Request $request)
    {
        $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
        $doc = $request->input('tercero');
        $ahora = Carbon::now();
        if($tercero != null){
            $facturas = Factura::with('productos.contrapartida')->where('cruzada', 0)->where('tipo', 'Venta')->doesnthave('credito')->where('terceros_id', $tercero->id)->get();
            $creditos = Credito::with('factura.tercero', 'cuotas', 'placa')->where('estado', 'En cobro')->whereHas('cliente', function($q) use($doc){$q->where('nro_identificacion', $doc);})->get();
        }else{
            $facturas = Factura::with('productos.contrapartida')->where('cruzada', 0)->where('tipo', 'Venta')->doesnthave('credito')->get();
            $creditos = Credito::with('factura.tercero', 'cuotas', 'placa')->where('estado', 'En cobro')->get();
        }
        
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font'=>['bold'=>true]];

        $sheet->setCellValue("A1", "CARTERA GENERICA,  " . $ahora->format('Y-m-d H:i:s') . ",  " . $doc);
        $sheet->mergeCells("A1:L1");

        $sheet->setCellValue("A2", "Identificación");
        $sheet->setCellValue("B2", "Nombre");
        $sheet->setCellValue("C2", "Placa");
        $sheet->setCellValue("D2", "Factura");
        $sheet->setCellValue("E2", "Fecha");
        $sheet->setCellValue("F2", "Valor");
        $sheet->setCellValue("G2", "Interés Corriente");
        $sheet->setCellValue("H2", "Abono");
        $sheet->setCellValue("I2", "Saldo");
        $sheet->setCellValue("J2", "Vencido");
        $sheet->setCellValue("K2", "Interés mora(todas las cuotas)");
        $sheet->setCellValue("L2", "Mora(cuota más antigua)");
        $sheet->setCellValue("M2", "Edad mora(cuota más antigua)");
        $sheet->getStyle("A1:M2")->applyFromArray($styleCentrar);
        
        $i = 3;
        foreach ($creditos as $credito) {
            $sheet->setCellValue("A".$i, $credito->factura->tercero->documento);
            $sheet->setCellValue("B".$i, $credito->factura->tercero->nombre);
            $sheet->setCellValue("C".$i, $credito->placas);
            $sheet->setCellValue("D".$i, $credito->factura->prefijo . " " . $credito->factura->numero);
            $sheet->setCellValue("E".$i, $credito->factura->fecha);
            $sheet->setCellValue("F".$i, $credito->factura->valor);
            $abono = 0;
            $saldo = 0;
            $vencido = 0;
            $interes = 0;
            $mora = 0;
            $intmora = 0;
            foreach ($credito->cuotas as $cuota) {
                if($cuota->estado == "Pagada"){
                    $abono = $abono + $cuota->abono_capital + $cuota->interes;
                }elseif($cuota->estado == "Vencida"){
                    if($mora == 0) {
                        $mora = $cuota->mora;
                    }
                    if($cuota->saldo_interes < $cuota->interes){
                        $abono = $abono + $cuota->interes - $cuota->saldo_interes;
                    }
                    if($cuota->saldo_capital < $cuota->abono_capital){
                        $abono = $abono + $cuota->abono_capital - $cuota->saldo_capital;
                    }
                    if($credito->antiguo == "1"){
                        $vencido = $vencido + $cuota->saldo_capital + $cuota->saldo_interes;
                    }else{
                        $interes = $interes + $cuota->saldo_interes;
                        $vencido = $vencido + $cuota->saldo_capital;
                    }
                    $intmora = $intmora + $cuota->saldo_mora;
                }elseif($cuota->estado == "Vigente" || $cuota->estado == "Pendiente"){
                    if($cuota->saldo_interes < $cuota->interes){
                        $abono = $abono + $cuota->interes - $cuota->saldo_interes;
                    }
                    if($cuota->saldo_capital < $cuota->abono_capital){
                        $abono = $abono + $cuota->abono_capital - $cuota->saldo_capital;
                    }
                    if($credito->antiguo == "1"){
                        $saldo = $saldo + $cuota->saldo_capital + $cuota->saldo_interes;
                    }else{
                        $interes = $interes + $cuota->saldo_interes;
                        $saldo = $saldo + $cuota->saldo_capital;
                    }
                }
                if($credito->antiguo == "1"){
                    $interes = $interes + $cuota->interes;
                } 
            }
            $sheet->setCellValue("G".$i, $interes);
            $sheet->setCellValue("H".$i, $abono);
            $sheet->setCellValue("I".$i, $saldo+$vencido);
            $sheet->setCellValue("J".$i, $vencido);
            $sheet->setCellValue("K".$i, $intmora);
            $sheet->setCellValue("L".$i, $mora);
            if($mora == 0){
                $sheet->setCellValue("M".$i, "0");
            }elseif ($mora > 0 && $mora <= 30) {
                $sheet->setCellValue("M".$i, "1 a 30");
            }elseif ($mora > 30 && $mora <= 60) {
                $sheet->setCellValue("M".$i, "31 a 60");
            }elseif ($mora > 60 && $mora <= 90) {
                $sheet->setCellValue("M".$i, "61 a 90");
            }elseif ($mora > 90 && $mora <= 120) {
                $sheet->setCellValue("M".$i, "91 a 120");
            }elseif ($mora > 120 && $mora <= 150) {
                $sheet->setCellValue("M".$i, "121 a 150");
            }elseif ($mora > 150 && $mora <= 180) {
                $sheet->setCellValue("M".$i, "151 a 180");
            }else{
                $sheet->setCellValue("M".$i, "Más de 180");
            }
            $i++;
        }
        foreach ($facturas as $factura) {
            $recibos = FacturasxRecibo::where('facturas_id', $factura->id)->whereHas('recibo', function($q){$q->where('estado', 'Activo');})->latest('id')->get();
            $abono = 0;
            $saldo = 0;
            if(count($recibos) == 0){
                $ncreds = Nota::with('movimientos')->where('prefijo', 'NC')->where('facturas_id', $factura->id)->get();
                $contras = [];
                foreach ($factura->productos as $producto) {
                    if(!in_array($producto->contrapartida->id, $contras)){
                        $movimiento = Movimiento::where('facturas_id', $factura->id)->where('cuentas_id', $producto->contrapartida->id)->first();
                        if($movimiento != null){
                            $snotas = 0; 
                            foreach ($ncreds as $ncred) {
                                foreach ($ncred->movimientos as $ncmov) {
                                    if($producto->contrapartida->id == $ncmov->cuentas_id){
                                       $snotas = $snotas + $ncmov->valor;
                                       break;
                                    } 
                                }
                            }
                            $saldo = $saldo + $movimiento->valor - $snotas;
                        }else{
                            $saldo = $factura->saldo;
                            break;
                        }
                        $contras[] = $producto->contrapartida->id;
                    }
                }
            }else{
                $saldo = $recibos[0]->saldo;
                foreach ($recibos as $recibo) {
                    $abono = $abono + ($recibo->abono - $recibo->mora);
                }
            }
            $sheet->setCellValue("A".$i, $factura->tercero->documento);
            $sheet->setCellValue("B".$i, $factura->tercero->nombre);
            $sheet->setCellValue("C".$i, $factura->placa);
            $sheet->setCellValue("D".$i, $factura->prefijo . " " . $factura->numero);
            $sheet->setCellValue("E".$i, $factura->fecha);
            $sheet->setCellValue("F".$i, $factura->valor);
            $sheet->setCellValue("G".$i, 0);
            $sheet->setCellValue("H".$i, $abono);
            $sheet->setCellValue("I".$i, $saldo);
            $sheet->setCellValue("J".$i, 0);
            $sheet->setCellValue("K".$i, $factura->mora);
            $sheet->setCellValue("L".$i, 0);
            $sheet->setCellValue("M".$i, "0");
            $i++;
        }
        $sheet->setCellValue("F".$i, "=SUM(F3:F" . ($i-1) . ")");
        $sheet->setCellValue("G".$i, "=SUM(G3:G" . ($i-1) . ")");
        $sheet->setCellValue("H".$i, "=SUM(H3:H" . ($i-1) . ")");
        $sheet->setCellValue("I".$i, "=SUM(I3:I" . ($i-1) . ")");
        $sheet->setCellValue("J".$i, "=SUM(J3:J" . ($i-1) . ")");
        $sheet->setCellValue("K".$i, "=SUM(K3:K" . ($i-1) . ")");

        foreach (range('A', 'M') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Cartera.xlsx');
        $archivo = file_get_contents('Cartera.xlsx');
        unlink('Cartera.xlsx');

        return base64_encode($archivo);
    }

    public function formBalance()
    {
        $fecha = Carbon::now();

        return view('informes.balance', compact('fecha'));
    }

    public function descargarBalance(Request $request)
    {
        set_time_limit(0);
        $fechain = $request->input('fechain') . ' 00:00';
        $fechafi = $request->input('fechafi') . ' 23:59';
        $fechaYear = Carbon::parse($fechain)->setMonth(1)->setDay(1);
        if($request->filled('cuentain')){
            $cuentain = $request->input('cuentain');
        }else{
            $cuentain = '0';
        }
        if($request->filled('cuentafi')){
            $cuentafi = $request->input('cuentafi');
        }else{
            $cuentafi = '6';
        }

        $cuentas = Cuenta::whereBetween('codigo', [$cuentain, $cuentafi])->has('movimientos')->orderBy('codigo', 'asc')->get();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font'=>['bold'=>true]];
        $styleNumero = ['numberFormat'=> ['formatCode'=> '#,##0']];
        $styleLetra = ['font'=>['size'=>8]];

        $sheet->setCellValue("A1", "BALANCE CONSOLIDADO CAHORS S.A.S DESDE " . $fechain . " HASTA " . $fechafi);
        $sheet->mergeCells("A1:F1");

        $sheet->setCellValue("A2", "Cuenta");
        $sheet->setCellValue("B2", "Nombre");
        $sheet->setCellValue("C2", "Saldo anterior");
        $sheet->setCellValue("D2", "Débitos");
        $sheet->setCellValue("E2", "Créditos");
        $sheet->setCellValue("F2", "Saldo");
        $sheet->getStyle("A1:F2")->applyFromArray($styleCentrar);

        $i = 3;
        foreach ($cuentas as $cuenta) {
            $j = $i;
            $i++;

            $debitos = 0;
            $creditos = 0;
            $cuentaPuc = substr($cuenta->codigo, 0, 1);
            if($request->input('terceros') == "1"){
                $movimientos = Movimiento::select('id', 'fecha', 'cuentas_id', 'terceros_id', DB::raw('SUM(CASE WHEN naturaleza="Crédito" THEN valor ELSE 0 END) as creditos'), DB::raw('SUM(CASE WHEN naturaleza="Débito" THEN valor ELSE 0 END) as debitos'))->
                with('tercero')->whereBetween('fecha', [$fechain, $fechafi])->where('cuentas_id', $cuenta->id)->where('estado', '1')->groupBy('terceros_id')->get();
                foreach ($movimientos as $movimiento) {
                    if ($cuentaPuc == '4' || $cuentaPuc == '5') {
                        $debantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Débito")->where('estado', '1')->where('fecha', '<', $fechain)->where('fecha', '>=', $fechaYear)->where('terceros_id', $movimiento->tercero->id)->sum('valor');
                        $creantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Crédito")->where('estado', '1')->where('fecha', '<', $fechain)->where('fecha', '>=', $fechaYear)->where('terceros_id', $movimiento->tercero->id)->sum('valor');
                    }else{
                        $debantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Débito")->where('estado', '1')->where('fecha', '<', $fechain)->where('terceros_id', $movimiento->tercero->id)->sum('valor');
                        $creantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Crédito")->where('estado', '1')->where('fecha', '<', $fechain)->where('terceros_id', $movimiento->tercero->id)->sum('valor');
                    }
                    $sheet->setCellValue("B".$i, $movimiento->tercero->documento . ' - '. $movimiento->tercero->nombre);
                    $sheet->setCellValue("C".$i, $debantes-$creantes);
                    $sheet->setCellValue("F".$i, ($debantes - $creantes) + ($movimiento->debitos - $movimiento->creditos));
                    $sheet->setCellValue("D".$i, $movimiento->debitos);
                    $sheet->setCellValue("E".$i, $movimiento->creditos);
                    
                    $debitos = $debitos + $movimiento->debitos;
                    $creditos = $creditos + $movimiento->creditos;
                    $i++;
                }
            }else{
                $debitos = Movimiento::select('id', 'fecha', 'valor', 'cuentas_id', 'naturaleza')->where('naturaleza', 'Débito')->
                    whereBetween('fecha', [$fechain, $fechafi])->where('cuentas_id', $cuenta->id)->where('estado', '1')->sum('valor');
                $creditos = Movimiento::select('id', 'fecha', 'valor', 'cuentas_id', 'naturaleza')->where('naturaleza', 'Crédito')->
                    whereBetween('fecha', [$fechain, $fechafi])->where('cuentas_id', $cuenta->id)->where('estado', '1')->sum('valor');
                //$i++;
            }       

            $idcuenta = $cuenta->id;
            $nomovs = TerceroCahors::whereHas('movimientos', function($q) use($idcuenta){$q->where('cuentas_id', $idcuenta)->where('estado', '1');})->whereDoesntHave('movimientos', function($q) use($fechain, $fechafi, $idcuenta){$q->whereBetween('fecha', [$fechain, $fechafi])->where('estado', '1')->where('cuentas_id', $idcuenta);})->get();
            if($request->input('terceros') == "1"){
                foreach ($nomovs as $nomov) {
                    if ($cuentaPuc == '4' || $cuentaPuc == '5') {
                        $debantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Débito")->where('estado', '1')->where('fecha', '<', $fechain)->where('fecha', '>=', $fechaYear)->where('terceros_id', $nomov->id)->sum('valor');
                        $creantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Crédito")->where('estado', '1')->where('fecha', '<', $fechain)->where('fecha', '>=', $fechaYear)->where('terceros_id', $nomov->id)->sum('valor');
                    }else {
                        $debantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Débito")->where('estado', '1')->where('fecha', '<', $fechain)->where('terceros_id', $nomov->id)->sum('valor');
                        $creantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Crédito")->where('estado', '1')->where('fecha', '<', $fechain)->where('terceros_id', $nomov->id)->sum('valor');
                    }
                    $sheet->setCellValue("B".$i, $nomov->documento . ' - '. $nomov->nombre);
                    $sheet->setCellValue("C".$i, $debantes-$creantes);
                    $sheet->setCellValue("F".$i, ($debantes - $creantes));
                    $sheet->setCellValue("D".$i, 0);
                    $sheet->setCellValue("E".$i, 0);
                    $i++;
                }
            }
            if ($cuentaPuc == '4' || $cuentaPuc == '5') {
                $debantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Débito")->where('estado', '1')->where('fecha', '<', $fechain)->where('fecha', '>=', $fechaYear)->sum('valor');
                $creantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Crédito")->where('estado', '1')->where('fecha', '<', $fechain)->where('fecha', '>=', $fechaYear)->sum('valor');
            }else {
                $debantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Débito")->where('estado', '1')->where('fecha', '<', $fechain)->sum('valor');
                $creantes = Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', "Crédito")->where('estado', '1')->where('fecha', '<', $fechain)->sum('valor');
            }
            $sheet->setCellValue("A".$j, $cuenta->codigo);
            $sheet->setCellValue("B".$j, $cuenta->nombre);
            $sheet->setCellValue("C".$j, $debantes-$creantes);
            $sheet->setCellValue("F".$j, ($debantes-$creantes) + ($debitos-$creditos));
            $sheet->setCellValue("D".$j, $debitos);
            $sheet->setCellValue("E".$j, $creditos);
            
            $sheet->getStyle("A".$j . ":F".$j)->applyFromArray($styleCentrar);
        }

        $sheet->getStyle("C3:F".$i)->applyFromArray($styleNumero);
        $sheet->getStyle("A3:F".$i)->applyFromArray($styleLetra);
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Balance.xlsx');
        $archivo = file_get_contents('Balance.xlsx');
        unlink('Balance.xlsx');

        return base64_encode($archivo);
    }

    public function formSaldoPorCuenta()
    {
        $fecha = Carbon::now();

        return view('informes.saldosPorCuenta', compact('fecha'));
    }

    public function saldosPorCuenta(Request $request)
    {
        $cuenta = $request->input('cuenta');
        $idcuenta = Cuenta::where('codigo', $cuenta)->first()->id;
        $fechafi = $request->input('fechafi') . ' 23:59';
        $terceros = TerceroCahors::with(['movimientos' => function($q) use($idcuenta, $fechafi){$q->where('cuentas_id', $idcuenta)->where('fecha', '<=', $fechafi);}])->get();

        return view('informes.saldosPorCuenta', compact('terceros', 'cuenta', 'fechafi'));
    }
    public function formCreditosEnCobro(){
        return view('informes.creditosEnCobro');
    }
    public function descargarCreditosEnCobro(Request $request){
        $rango = [$request->input('fechainicial'), $request->input('fechafinal')];
        // $creditos = Credito::with('factura', 'cuotas', 'cliente', ['pagos'=>function($q) use($rango){$q->select('id', 'valor', 'fecha', 'creditos_id')->with('cuotas')->whereBetween('fecha', $rango);}])->get();
        $creditos = Credito::with(['pagos'=>function($q) use($rango){$q->select('id', 'valor', 'fecha', 'creditos_id')->with('cuotas')->whereBetween('fecha', $rango);}, 
                'cuotas'=>function($q){$q->select('id', 'fecha_vencimiento','abono_capital', 'mora', 'estado', 'saldo_interes', 'saldo_mora', 'saldo_capital', 'creditos_id');}, 
                'factura'=>function($q){$q->select('id', 'prefijo', 'numero', 'creditos_id');}, 
                'cliente'=>function($q){$q->select('id', 'nro_identificacion', 'primer_apellido', 'segundo_apellido', 'primer_nombre', 'segundo_nombre', 'direccion', 'email', 'celular');}])->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]];
        $sheet->setCellValue("A1", "CREDITOS EN COBRO");
        $sheet->mergeCells("A1:S1");

        $sheet->setCellValue("A2", "Identificacion");
        $sheet->setCellValue("B2", "Nombre");
        $sheet->setCellValue("C2", "Placa");
        $sheet->setCellValue("D2", "Estado");
        $sheet->setCellValue("E2", "Factura");
        $sheet->setCellValue("F2", "Celular");
        $sheet->setCellValue("G2", "Fecha");
        $sheet->setCellValue("H2", "Vencimiento");
        $sheet->setCellValue("I2", "Mora");
        $sheet->setCellValue("J2", "Int. Mora");
        $sheet->setCellValue("K2", "Edad");
        $sheet->setCellValue("L2", "Valor");
        $sheet->setCellValue("M2", "Cuota");
        $sheet->setCellValue("N2", "Saldo 1");
        $sheet->setCellValue("O2", "Abono");
        $sheet->setCellValue("P2", "Saldo 2");
        $sheet->setCellValue("Q2", "Observacion");
        $sheet->setCellValue("R2", "Observacion 2");
        $sheet->setCellValue("S2", "Fecha 2");
        $sheet->setCellValue("T2", "Destino");
        $sheet->getStyle("A1:T2")->applyFromArray($styleCentrar);

        $i = 3;
        
        

        foreach ($creditos as $credito){
            $mora = 0;
            $valorMora = 0;
            $cuotaSinMora = 0;
            $abono = 0;
            $sheet->setCellValue("A".$i, $credito->cliente->nro_identificacion);
            $nombreCompleto = $credito->cliente->primer_nombre . " " . $credito->cliente->segundo_nombre . " " . 
                              $credito->cliente->primer_apellido . " " . $credito->cliente->segundo_apellido;
            $sheet->setCellValue("B".$i, $nombreCompleto);
            $sheet->setCellValue("C".$i, $credito->placas);
            $sheet->setCellValue("D".$i, $credito->estado);
            
            $sheet->setCellValue("F".$i, $credito->cliente->celular);
            $sheet->setCellValue("G".$i, $credito->fecha_prestamo);

            $numCuotas = count($credito->cuotas);
            foreach($credito->cuotas as $cuota){
                
                if($cuota->estado == "Pagada"){
                    $sigcuota = $cuota->ncuota + 1;
                    if($cuota->ncuota == $sigcuota){
                        $sheet->setCellValue("H". $i, $cuota->fecha_vencimiento);
                    }
                    // elseif($i == $numCuotas){
                    //     $sheet->setCellValue("H". $i, $cuota->fecha_vencimiento);
                    // }
                    
                    $abono = $abono + $cuota->abono_capital - $cuota->saldo_interes;
                }
                elseif($cuota->estado == "Vencida"){
                    $valorMora = $valorMora + $cuota->saldo_capital + $cuota->saldo_interes;
                    $mora = $cuota->mora;
                }
                else{
                    $sheet->setCellValue("H". $i, $cuota->fecha_vencimiento );
                    if($cuota->saldo_interes < $cuota->interes){
                        $abono = $abono + $cuota->interes - $cuota->saldo_interes;
                    }
                    if($cuota->saldo_capital < $cuota->abono_capital){
                        $abono = $abono + $cuota->abono_capital - $cuota->saldo_capital;
                    }
                }

                
                $numeroFactura = $credito->factura->prefijo . " " . $credito->factura->numero;
                $sheet->setCellValue("E".$i, $numeroFactura);
                $sheet->setCellValue("J". $i, $cuota->interes_mora);
                $edadMora = $this->calcularEdadMora($mora);
                $sheet->setCellValue("K". $i, $edadMora);
                $valorCuota = $cuota->abono_capital + $cuota->interes + $cuota->interes_mora;
                $sheet->setCellValue("M". $i, $valorCuota);
                $cuotaSinMora = $cuotaSinMora + $cuota->abono_capital + $cuota->interes;

                foreach($credito->pagos as $pago){
                    $sheet->setCellValue("Q". $i, $pago->observaciones);
                    $sheet->setCellValue("S". $i, $pago->fecha);
                    
                }

            
            
            
            }
            $sheet->setCellValue("I". $i, $mora);
            $sheet->setCellValue("L". $i, $credito->monto_total);
            $sheet->setCellValue("N". $i, $cuotaSinMora);
            $sheet->setCellValue("O". $i, $abono);
            $sheet->setCellValue("P". $i, $cuotaSinMora - $abono);
            $sheet->setCellValue("T". $i, $credito->tipo);
            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Creditos.xlsx');
        $archivo = file_get_contents('Creditos.xlsx');
        unlink('Creditos.xlsx');

        return base64_encode($archivo);

    }
    private function calcularEdadMora($mora){
        switch ($mora) {
            case 0:
                return "0";
            case ($mora >0 && $mora <= 30):
                return "1 a 30";
            case ($mora > 30 && $mora <= 60):
                return "31 a 60";
            case ($mora > 60 && $mora <= 90):
                return "61 a 90";
            case ($mora > 90 && $mora <= 120):
                return "91 a 120";
            case ($mora > 120 && $mora <= 150):
                return "121 a 150";
            case ($mora > 150 && $mora <= 180):
                return "151 a 180";
            case ($mora > 181):
                return "Mas de 180";
            default:
                # code...
                break;
        }
    }
}
