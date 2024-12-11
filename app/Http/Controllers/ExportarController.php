<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Factura;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportarController extends Controller
{
    public function creditosEnCobro(Request $request)
    {  
        if ($request->filled('id')) {
            $id = $request->input('id');
            $creditos = Credito::with('cliente')->where('id', $id)->where('estado', 'En cobro')->get();
        }elseif($request->filled('identificacion')){
            $ide = $request->input('identificacion');
            $creditos = Credito::with('cliente')->whereHas('cliente', function($q) use($ide){$q->where('nro_identificacion', $ide);})->where('estado', 'En cobro')->get();
        }elseif($request->filled('nombre')){
            $nombre = $request->input('nombre');
            $creditos = Credito::with('cliente')->where('estado', 'En cobro')->whereHas('factura', function($q) use($nombre){$q->whereHas('tercero', function($r) use($nombre){$r->where('nombre', 'like', '%' . $nombre . '%');});})->get();
        }elseif($request->filled('tipo')){
            $tipo = $request->input('tipo');
            $creditos = Credito::with('cliente')->where('estado', 'En cobro')->where('tipo', $tipo)->get();
        }else{
            $creditos = Credito::with('cliente')->where('estado', 'En cobro')->get();
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font'=>['bold'=>true]];

        $sheet->setCellValue("A1", "Créditos en Cobro");
        $sheet->mergeCells("A1:H1");
        
        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Identificación");
        $sheet->setCellValue("C2", "Nombre");
        $sheet->setCellValue("D2", "Monto");
        $sheet->setCellValue("E2", "Cuotas");
        $sheet->setCellValue("F2", "Tasa");
        $sheet->setCellValue("G2", "Desembolso");
        $sheet->setCellValue("H2", "Tipo");
        $sheet->getStyle("A1:H2")->applyFromArray($styleCentrar);

        $i = 3;
        foreach ($creditos as $credito) {
            $sheet->setCellValue("A".$i, $credito->numero);
            $sheet->setCellValue("B".$i, $credito->cliente->nro_identificacion);
            $sheet->setCellValue("C".$i, $credito->cliente->primer_nombre . " " . $credito->cliente->primer_apellido);
            $sheet->setCellValue("D".$i, $credito->monto_total);
            $sheet->setCellValue("E".$i, $credito->plazo);
            $sheet->setCellValue("F".$i, $credito->tasa);
            $sheet->setCellValue("G".$i, $credito->fecha_prestamo);
            $sheet->setCellValue("H".$i, $credito->tipo);
            $i++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('Cobro.xlsx'));
        $archivo = file_get_contents(storage_path('Cobro.xlsx'));
        unlink(storage_path('Cobro.xlsx'));

        return base64_encode($archivo);
    }

    public function facturasVenta(Request $request)
    {
        $fechas = explode(" - ", $request->input('fecha'));
        $cliente = $request->input('cliente');
        $estado = $request->input('estado');
        $concepto = $request->input('concepto');

        if ($request->filled('numero')) {
            $facturas = Factura::with('credito.cliente')->where('tipo', 'Venta')->where('numero', $request->input('numero'))->get();
        }else{   
            if($request->filled('fecha') && $request->filled('cliente')){
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'Venta')->whereBetween('fecha', $fechas)->whereHas('credito', function($q) use($cliente){$q->whereHas('cliente', function ($r) use($cliente){$r->where('nro_identificacion', $cliente);});})->get();
            }elseif ($request->filled('fecha')) {
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'Venta')->whereBetween('fecha', $fechas)->get();
            }elseif ($request->filled('cliente')) {
                $terce = $request->input('cliente');
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'Venta')->whereHas('tercero', function($r) use($terce){$r->where('documento', $terce)->orWhere('nombre', 'like', '%' . $terce . '%');})->get();
            }elseif($request->filled('estado')){
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'venta')->where('cruzada', $estado)->doesntHave('credito')->get();
            }elseif($request->filled('concepto')){
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'venta')->where('descripcion', 'like', '%' . $concepto . '%')->get();
            }else{
                return redirect('/contabilidad/facturas/ventas');
            }
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font'=>['bold'=>true]];

        $sheet->setCellValue("A1", "Facturas de Venta");
        $sheet->mergeCells("A1:F1");
        
        $sheet->setCellValue("A2", "Número");
        $sheet->setCellValue("B2", "Fecha");
        $sheet->setCellValue("C2", "Concepto");
        $sheet->setCellValue("D2", "Valor");
        $sheet->setCellValue("E2", "Tercero");
        $sheet->setCellValue("F2", "Estado");
        $sheet->getStyle("A1:F2")->applyFromArray($styleCentrar);

        $i = 3;
        foreach ($facturas as $factura) {
            $sheet->setCellValue("A".$i, $factura->prefijo . " " . $factura->numero);
            $sheet->setCellValue("B".$i, $factura->fecha);
            $sheet->setCellValue("C".$i, $factura->descripcion);
            $sheet->setCellValue("D".$i, $factura->valor);
            $sheet->setCellValue("E".$i, $factura->tercero->documento . ", " . $factura->tercero->nombre);
            if($factura->cruzada == 1){
                $sheet->setCellValue("F".$i, "Cobrada");
            }else{
                $sheet->setCellValue("F".$i, "Pendiente");
            }
            $i++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Ventas.xlsx');
        $archivo = file_get_contents('Ventas.xlsx');
        unlink('Ventas.xlsx');

        return base64_encode($archivo);
    }

    public function clientes()
    {
        $clientes = User::get();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $styleCentrar = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font'=>['bold'=>true]];

        $sheet->setCellValue("A1", "Clientes Cahors");
        $sheet->mergeCells("A1:H1");
        
        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Identificación");
        $sheet->setCellValue("C2", "Nombre");
        $sheet->setCellValue("D2", "Celular");
        $sheet->setCellValue("E2", "Email");
        $sheet->setCellValue("F2", "Dirección");
        $sheet->setCellValue("G2", "Municipio");
        $sheet->setCellValue("H2", "Condición");
        $sheet->getStyle("A1:H2")->applyFromArray($styleCentrar);

        $i = 3;
        foreach ($clientes as $cliente) {
            $sheet->setCellValue("A".$i, $cliente->id);
            $sheet->setCellValue("B".$i, $cliente->nro_identificacion);
            $sheet->setCellValue("C".$i, $cliente->primer_nombre . " " . $cliente->segundo_nombre ." " . $cliente->primer_apellido. " " . $cliente->segundo_apellido);
            $sheet->setCellValue("D".$i, $cliente->celular);
            $sheet->setCellValue("E".$i, $cliente->email);
            $sheet->setCellValue("F".$i, $cliente->direccion);
            $sheet->setCellValue("G".$i, $cliente->municipio);
            $sheet->setCellValue("H".$i, $cliente->condicion);
            $i++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('Clientes.xlsx'));
        $archivo = file_get_contents(storage_path('Clientes.xlsx'));
        unlink(storage_path('Clientes.xlsx'));

        return base64_encode($archivo);
    }
}
