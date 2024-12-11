<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Credito;
use App\Models\Factura;
use App\Models\Nota;
use App\Models\NtaContabilidad;
use App\Models\Pago;
use App\Models\Recibo;
use App\Models\TerceroJADMIN;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FiltroController extends Controller
{
    public function filtrarCreditosCobro(Request $request)
    {
        $num = $request->input('numero');
        $ide = $request->input('identificacion');
        $nombre = $request->input('nombre');
        $tipo = $request->input('tipo');
        if ($request->filled('numero')) {
            $creditos = Credito::with('cliente')->where('numero', $num)->where('estado', 'En cobro')->paginate(10);
            $filtro = ['numero', $num];
        }elseif($request->filled('identificacion')){
            $creditos = Credito::with('cliente')->whereHas('cliente', function($q) use($ide){$q->where('nro_identificacion', $ide);})->where('estado', 'En cobro')->paginate(10);
            $filtro =  ['ide', $ide];
        }elseif($request->filled('nombre')){
            $creditos = Credito::with('cliente')->where('estado', 'En cobro')->whereHas('factura', function($q) use($nombre){$q->whereHas('tercero', function($r) use($nombre){$r->where('nombre', 'like', '%' . $nombre . '%');});})->paginate(10);
            $filtro =  ['nombre', $nombre];
        }elseif($request->filled('tipo')){
            $creditos = Credito::with('cliente')->where('estado', 'En cobro')->where('tipo', $tipo)->paginate(10);
            $filtro = ['tipo', $tipo];
        }else{
            return redirect('creditos_cobro');
        }

        $creditos->appends($request->query());

        return view('creditos.cobro', compact('creditos', 'filtro'));
    }

    public function filtrarCreditosAprobados(Request $request)
    {
        $num = $request->input('numero');
        $ide = $request->input('identificacion');
        if ($request->filled('numero')) {
            $creditos = Credito::with('cliente')->where('numero', $num)->where('estado', function($q){$q->where('estado', 'En cobro')->orWhere('estado', 'Proceso');})->paginate(10);
        }elseif($request->filled('identificacion')){
            $creditos = Credito::with('cliente')->where('estado', 'Aprobado')->whereHas('cliente', function($q) use($ide){$q->where('nro_identificacion', $ide);})->paginate(10);
        }else{
            return redirect('creditos_aprobados');
        }

        $creditos->appends($request->query());

        return view('creditos.aprobados', compact('creditos', 'num', 'ide'));
    }

    public function filtrarCreditosFinalizados(Request $request)
    {
        $num = $request->input('numero');
        $ide = $request->input('identificacion');
        $est = $request->input('estado');
        if ($request->filled('numero')) {
            $creditos = Credito::with('cliente')->where('numero', $num)->where(function($q){$q->where('estado', 'Finalizado')->orWhere('estado', 'Rechazado');})->paginate(10);
        }elseif($request->filled('identificacion') && $request->filled('estado')){       
            $creditos = Credito::with('cliente')->whereHas('cliente', function($q) use($ide){$q->where('nro_identificacion', $ide);})->where('estado', $request->input('estado'))->paginate(10);
        }elseif($request->filled('identificacion')){
            $creditos = Credito::with('cliente')->whereHas('cliente', function($q) use($ide){$q->where('nro_identificacion', $ide);})->where(function($q){$q->where('estado', 'Finalizado')->orWhere('estado', 'Rechazado');})->paginate(10);
        }elseif($request->filled('estado')){
            $creditos = Credito::with('cliente')->where('estado', $request->input('estado'))->paginate(10);
        }else{
            return redirect('creditos_finalizados');
        }

        $creditos->appends($request->query());

        return view('creditos.finalizados', compact('creditos', 'num', 'ide', 'est'));
    }

    public function filtrarSolicitudes(Request $request)
    {
        $ide = $request->input('identificacion');
        if($request->filled('identificacion')){
            $solicitudes = Credito::with('cliente')->where(function($q){$q->where('estado', 'Solicitado')->orWhere('estado', 'Evaluando');})->whereHas('cliente', function($q) use($ide){$q->where('nro_identificacion', $ide);})->paginate(10);
        }else{
            return redirect('solicitudes_credito');
        }

        $solicitudes->appends($request->query());

        return view('solicitudes.lista', compact('solicitudes', 'ide'));
    }

    public function filtrarClientes(Request $request)
    {
        $ide = $request->input('identificacion');
        $cond = $request->input('condicion');
        if($request->filled('identificacion') && $request->filled('estado')){
            $clientes = User::where('rol', '2')->where('nro_identificacion', $ide)->where('condicion', $cond)->paginate(15);
        }elseif($request->filled('identificacion')){
            $clientes = User::where('rol', '2')->where('nro_identificacion', $ide)->paginate(15);
        }elseif($request->filled('condicion')){
            $clientes = User::where('rol', '2')->where('condicion', $cond)->paginate(15);
        }else{
            return redirect('clientes');
        }

        $clientes->appends($request->query());

        return view('clientes.lista', compact('clientes', 'ide', 'cond'));
    }

    public function filtrarCartera(Request $request)
    {
        $ide = $request->input('identificacion');
        if($request->filled('identificacion')){
            $clientes = User::with(['creditos'=>function($q){$q->with('cuotas', function($r){$r->where('estado', 'Vencida');});}])->where('nro_identificacion', $ide)->whereHas('creditos', function($q){$q->whereHas('cuotas', function($r){$r->where('estado', 'Vencida');});})->paginate(10)->appends($request->query());
        }else{
            return redirect('cartera');
        }

        return view('cartera.lista', compact('clientes', 'ide'));
    }

    public function filtrarPagos(Request $request)
    {
        $cliente = $request->input('cliente');
        $placa = $request->input('placa');
        if ($request->filled('placa') && $request->filled('cliente')) {
            $pagos = Pago::with(['cliente', 'credito.placa', 'recibo'])->whereHas('cliente', function($q) use($cliente){$q->where('nro_identificacion', $cliente);})->whereHas('credito', function($q) use($placa){$q->whereHas('placa', function($r) use($placa){$r->where('placa', $placa);});})->get();
        }elseif ($request->filled('placa')) {
            $pagos = Pago::with(['cliente', 'credito.placa', 'recibo'])->whereHas('credito', function($q) use($placa){$q->whereHas('placa', function($r) use($placa){$r->where('placa', $placa);});})->get();
        }elseif ($request->filled('cliente')) {
            $pagos = Pago::with(['cliente', 'credito.placa', 'recibo'])->whereHas('cliente', function($q) use($cliente){$q->where('nro_identificacion', $cliente);})->get();
        }else{
            return redirect('/pagos/realizados');
        }

        return view('pagos.lista', compact('pagos', 'cliente', 'placa'));
    }

    public function filtrarFacturasVenta(Request $request)
    {
        $numero = $request->input('numero');
        $fechas = explode(" - ", $request->input('fecha'));
        $fecha = "";
        $cliente = $request->input('cliente');
        $estado = $request->input('estado');
        $concepto = $request->input('concepto');

        if ($request->filled('numero')) {
            $facturas = Factura::with('credito.cliente')->where('tipo', 'Venta')->where('numero', $request->input('numero'))->get();
        }else{   
            if($request->filled('fecha') && $request->filled('cliente')){
                $fecha = $request->input('fecha');
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'Venta')->whereBetween('fecha', $fechas)->whereHas('credito', function($q) use($cliente){$q->whereHas('cliente', function ($r) use($cliente){$r->where('nro_identificacion', $cliente);});})->paginate(20)->appends($request->query());
            }elseif ($request->filled('fecha')) {
                $fecha = $request->input('fecha');
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'Venta')->whereBetween('fecha', $fechas)->paginate(20)->appends($request->query());
            }elseif ($request->filled('cliente')) {
                $terce = $request->input('cliente');
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'Venta')->whereHas('tercero', function($r) use($terce){$r->where('documento', $terce)->orWhere('nombre', 'like', '%' . $terce . '%');})->paginate(20)->appends($request->query());
            }elseif($request->filled('estado')){
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'venta')->where('cruzada', $estado)->doesntHave('credito')->paginate(20)->appends($request->query());
            }elseif($request->filled('concepto')){
                $facturas = Factura::with('credito', 'tercero')->where('tipo', 'venta')->where('descripcion', 'like', '%' . $concepto . '%')->paginate(20)->appends($request->query());
            }else{
                return redirect('/contabilidad/facturas/ventas');
            }
        }

        return view('facturas.listaVentas', compact('facturas', 'numero', 'fecha', 'cliente', 'estado', 'concepto'));   
    }

    public function filtrarFacturasCompra(Request $request)
    {
        $fechaenv = Carbon::now();
        $envyear = $request->input('envyear');
        $numero = $request->input('numero');
        $fechas = explode(" - ", $request->input('fecha'));
        $tercero = $request->input('tercero');
        $fecha = "";

        if ($request->filled('numero')) {
            $facturas = Factura::with('credito')->where('tipo', 'Compra')->where('year', $envyear)->where('numero', $request->input('numero'))->get();
        }elseif($request->filled('fecha')){
            $fecha = $request->input('fecha');
            $facturas = Factura::with('credito')->where('tipo', 'Compra')->where('year', $envyear)->whereBetween('fecha', $fechas)->paginate(20)->appends($request->query());
        }elseif($request->filled('tercero')){
            $facturas = Factura::with('credito')->where('tipo', 'Compra')->where('year', $envyear)->whereHas('tercero', function($q) use($tercero){$q->where('documento', $tercero)->orWhere('nombre', 'like', '%'.$tercero.'%');})->paginate(20)->appends($request->query());
        }else{
            return redirect('/contabilidad/facturas/compras');
        }

        return view('facturas.listaCompras', compact('facturas', 'numero', 'fecha', 'tercero', 'fechaenv', 'envyear'));   
    }

    public function filtrarNotasDebito(Request $request)
    {
        $numero = $request->input('numero');
        $fechas = explode(" - ", $request->input('fecha'));
        $fecha = "";
        $factura = $request->input('factura');

        if($request->filled('numero')){
            $notasDebito = Nota::with('factura')->where('tipo', 'Débito')->where('numero', $request->input('numero'))->get();
        }else{
            if ($request->filled('fecha') && $request->filled('factura')) {
                $fecha = $request->input('fecha');
                $notasDebito = Nota::with('factura')->where('tipo', 'Débito')->whereBetween('fecha', $fechas)->whereHas('factura', function($q) use($factura){$q->where('numero', 'like', $factura . '%');})->orderBy('id', 'desc')->paginate(15);
            }elseif ($request->filled('fecha')) {
                $fecha = $request->input('fecha');
                $notasDebito = Nota::with('factura')->where('tipo', 'Débito')->whereBetween('fecha', $fechas)->orderBy('id', 'desc')->paginate(15);
            }elseif ($request->filled('factura')) {
                $notasDebito = Nota::with('factura')->where('tipo', 'Débito')->whereHas('factura', function($q) use($factura){$q->where('numero', 'like', $factura . '%');})->orderBy('id', 'desc')->paginate(15);
            }else{
                return redirect('/contabilidad/notas_debito');
            }
        }

        return view('notas.listaDebito', compact('notasDebito', 'numero', 'fecha', 'factura'));
    }

    public function filtrarNotasCredito(Request $request)
    {
        $numero = $request->input('numero');
        $fechas = explode(" - ", $request->input('fecha'));
        $fecha = "";
        $factura = $request->input('factura');

        if($request->filled('numero')){
            $notasCredito = Nota::with('factura')->where('tipo', 'Crédito')->where('numero', $request->input('numero'))->get();
        }else{
            if ($request->filled('fecha') && $request->filled('factura')) {
                $fecha = $request->input('fecha');
                $notasCredito = Nota::with('factura')->where('tipo', 'Crédito')->whereBetween('fecha', $fechas)->whereHas('factura', function($q) use($factura){$q->where('numero', 'like', $factura . '%');})->orderBy('id', 'desc')->paginate(15);
            }elseif ($request->filled('fecha')) {
                $fecha = $request->input('fecha');
                $notasCredito = Nota::with('factura')->where('tipo', 'Crédito')->whereBetween('fecha', $fechas)->orderBy('id', 'desc')->paginate(15);
            }elseif ($request->filled('factura')) {
                $notasCredito = Nota::with('factura')->where('tipo', 'Crédito')->whereHas('factura', function($q) use($factura){$q->where('numero', 'like', $factura . '%');})->orderBy('id', 'desc')->paginate(15);
            }else{
                return redirect('/contabilidad/notas_credito');
            }
        }

        return view('notas.listaCredito', compact('notasCredito', 'numero', 'fecha', 'factura'));
    }

    public function filtrarNotasContables(Request $request)
    {
        $fechaenv = Carbon::now();
        $envyear = $request->input('envyear');
        $numero = $request->input('numero');
        $fechas = explode(" - ", $request->input('fecha'));
        $fecha = "";
        $prefijo = $request->input('prefijo');

        if($request->filled('numero')){
            $notas = NtaContabilidad::where('numero', $request->input('numero'))->where('year', $envyear)->orderBy('id', 'desc')->paginate(15);
        }elseif ($request->filled('fecha')) {
            $fecha = $request->input('fecha');
            $notas = NtaContabilidad::whereBetween('fecha', $fechas)->where('year', $envyear)->orderBy('id', 'desc')->paginate(15);
        }elseif ($request->filled('prefijo')) {
            $fecha = $request->input('fecha');
            $notas = NtaContabilidad::where('prefijo', $prefijo)->where('year', $envyear)->orderBy('id', 'desc')->paginate(15);
        }else{
            return redirect('/contabilidad/notas_contables');
        }

        return view('notas.listaContables', compact('notas', 'fecha', 'numero', 'prefijo', 'fechaenv', 'envyear'));
    }

    public function filtrarRecibos(Request $request)
    {
        $fecha = Carbon::now();
        $envyear = $request->input('envyear');
        if($request->filled('numero')){
            $recibos = Recibo::with('facturas', 'pago')->where('numero', $request->input('numero'))->where('year', $envyear)->get();
            $filtro = ["numero", $request->input('numero')];
        }elseif ($request->filled('fecha')) {
            $fechas = explode(" - ", $request->input('fecha'));
            $filtro = ["fecha", $request->input('fecha')];
            $recibos = Recibo::with('facturas', 'pago')->whereBetween('fecha', $fechas)->where('year', $envyear)->paginate(10);
        }elseif ($request->filled('facturas')) {
            $factura = $request->input('facturas');
            $filtro = ["facturas", $factura];
            $recibos = Recibo::with('facturas', 'pago')->whereHas('facturas', function($q) use($factura){$q->where('numero', $factura);})->where('year', $envyear)->paginate(10);
        }elseif ($request->filled('tercero')) {
            $terce = $request->input('tercero');
            $filtro = ["tercero", $terce];
            $recibos = Recibo::with('facturas', 'pago')->whereHas('facturas', function($q) use($terce){$q->whereHas('tercero', function($r) use($terce){$r->where('documento', $terce)->orWhere('nombre', 'like', '%' . $terce . '%');});})->where('year', $envyear)->paginate(10);
        }elseif ($request->filled('estado')) {
            $filtro = ["estado", $request->input('estado')];
            $recibos = Recibo::with('facturas', 'pago')->where('estado', $request->input('estado'))->where('year', $envyear)->paginate(10);
        }else{
            return redirect('/contabilidad/recibos');
        }

        if(method_exists($recibos, "links")){
            $recibos->appends($request->query());
        }
        
        return view('recibos.lista', compact('recibos', 'filtro', 'fecha', 'envyear'));
    }

    public function filtrarIngresos(Request $request)
    {
        $fecha = Carbon::now();
        $envyear = $request->input('envyear');
        if($request->filled('numero')){
            $ingresos = Comprobante::with('tercero')->where('tipo', 'Ingreso')->where('numero', $request->input('numero'))->where('year', $envyear)->paginate(10);
            $filtro = ['numero', $request->input('numero')];
        }elseif ($request->filled('fecha')) {
            $fechas = explode(" - ", $request->input('fecha'));
            $ingresos = Comprobante::with('tercero')->where('tipo', 'Ingreso')->whereBetween('fecha', $fechas)->where('year', $envyear)->paginate(10);
            $filtro = ['fecha', $request->input('fecha')];
        }elseif ($request->filled('tercero')) {
            $terce = $request->input('tercero');
            $ingresos = Comprobante::with('tercero')->where('tipo', 'Ingreso')->whereHas('tercero', function($r) use($terce){$r->where('documento', $terce)->orWhere('nombre', 'like', '%' . $terce . '%');})->where('year', $envyear)->paginate();
            $filtro = ['tercero', $request->input('tercero')];
        }elseif ($request->filled('estado')) {
            $ingresos = Comprobante::with('tercero')->where('tipo', 'Ingreso')->where('estado', $request->input('estado'))->where('year', $envyear)->paginate(10);
            $filtro = ['estado', $request->input('estado')];
        }else{
            return redirect('/contabilidad/ingresos');
        }
        if(method_exists($ingresos, "links")){
            $ingresos->appends($request->query());
        }

        return view('comprobantes.listaIngresos', compact('ingresos', 'filtro', 'fecha', 'envyear'));
    }

    public function filtrarEgresos(Request $request)
    {
        $fecha = Carbon::now();
        $envyear = $request->input('envyear');
        if($request->filled('numero')){
            $egresos = Comprobante::with('tercero')->where('tipo', 'Egreso')->where('numero', $request->input('numero'))->where('year', $envyear)->paginate(10);
            $filtro = ['numero', $request->input('numero')];
        }elseif ($request->filled('factura')) {
            $numfact = $request->input('factura');
            $egresos = Comprobante::with('tercero')->where('tipo', 'Egreso')->whereHas('factura', function($q) use($numfact){$q->where('numero', $numfact);})->where('year', $envyear)->paginate(10);
            $filtro = ['factura', $request->input('factura')];
        }elseif ($request->filled('fecha')) {
            $fechas = explode(" - ", $request->input('fecha'));
            $egresos = Comprobante::with('tercero')->where('tipo', 'Egreso')->whereBetween('fecha', $fechas)->where('year', $envyear)->paginate(10);
            $filtro = ['fecha', $request->input('fecha')];
        }elseif ($request->filled('tercero')) {
            $terce = $request->input('tercero');
            $egresos = Comprobante::with('tercero')->where('tipo', 'Egreso')->whereHas('tercero', function($r) use($terce){$r->where('documento', $terce)->orWhere('nombre', 'like', '%' . $terce . '%');})->where('year', $envyear)->paginate();
            $filtro = ['tercero', $request->input('tercero')];
        }elseif ($request->filled('estado')) {
            $egresos = Comprobante::with('tercero')->where('tipo', 'Egreso')->where('estado', $request->input('estado'))->where('year', $envyear)->paginate(10);
            $filtro = ['estado', $request->input('estado')];
        }else{
            return redirect('/contabilidad/egresos');
        }
        if(method_exists($egresos, "links")){
            $egresos->appends($request->query());
        }

        return view('comprobantes.listaEgresos', compact('egresos', 'filtro', 'fecha', 'envyear'));
    }

    public function filtrarTerceros(Request $request)
    {
        if($request->filled('identificacion')){
            $terceros = TerceroJADMIN::where('documento', $request->input('identificacion'))->get();
            $filtro = ['identificacion', $request->input('identificacion')];
        }elseif ($request->filled('nombre')) {
            $terceros = TerceroJADMIN::where('nombre', 'like', '%' . $request->input('nombre') . '%')->paginate(10);
            $filtro = ['nombre', $request->input('nombre')];
        }else{
            return redirect('/contabilidad/terceros');
        }

        if(method_exists($terceros, "links")){
            $terceros->appends($request->query());
        }

        return view('users.listaTerceros', compact('terceros', 'filtro'));
    }
}
