<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Recibo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReciboController extends Controller
{
    public function listaRecibos()
    {
        $fecha = Carbon::now();
        $envyear = $fecha->year;
        $recibos = Recibo::with('facturas', 'pago')->where('year', $fecha->year)->orderBy('id', 'desc')->paginate(10);

        return view('recibos.lista', compact('recibos', 'fecha', 'envyear'));
    }

    public function anularRecibo(Request $request)
    {
        try {
            DB::beginTransaction();

            $recibo = Recibo::with('pago.cuotas', 'movimientos.cuenta', 'facturas')->find($request->input('recibo'));
            $recibo->motivo = $request->input('motivo');
            $recibo->estado = "Inactivo";
            foreach ($recibo->movimientos as $movimiento) {
                $movimiento->estado = 0;
                $movimiento->save();
            }
            $recibo->save();

            if ($recibo->pago != null) {
                foreach ($recibo->pago->cuotas as $cuota) {
                    $cuota->estado = "Vigente";
                    $cuota->saldo_capital = $cuota->saldo_capital + $cuota->pivot->capital;
                    $cuota->saldo_interes = $cuota->saldo_interes + $cuota->pivot->interes;
                    if ($cuota->pivot->mora > 0) {
                        $cuota->estado = "Vencida";
                        $cuota->saldo_mora = $cuota->saldo_mora + $cuota->pivot->mora;
                        $cuota->interes_mora = $cuota->interes_mora - $cuota->pivot->mora;
                    }
                    $cuota->save();
                }

                $credito = Credito::with(['cuotas' => function ($q) {
                    $q->where('estado', 'Pagada');
                }])->find($recibo->pago->creditos_id);
                $credito->pagadas = count($credito->cuotas);
                if ($credito->estado == "Finalizado") {
                    $credito->estado = "En cobro";
                }
                $credito->save();
            }

            if (count($recibo->facturas) > 0) {
                foreach ($recibo->facturas as $factura) {
                    $factura->saldo = $factura->saldo + $factura->pivot->abono;
                    $factura->mora = $factura->mora + $factura->pivot->mora;
                    $factura->cruzada = 0;
                    $factura->save();
                }
            }
        } catch (Exception $ex) {
            DB::rollBack();
            
            return $ex->getMessage();
        }

        DB::commit();

        return redirect('/contabilidad/recibos');
    }

    public function detallesRecibo(Request $request, $recibo)
    {
        $recibo = Recibo::with('facturas', 'movimientos')->find($recibo);

        return view('recibos.detalles', compact('recibo'));
    }

    public function editarRecibo(Request $request, $recibo)
    {
        $recibo = Recibo::with('movimientos', 'facturas.tercero')->find($recibo);

        return view('recibos.edicionRecibo', compact('recibo'));
    }
}
