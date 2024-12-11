<?php

namespace App\Console\Commands;

use App\Models\Cuota;
use App\Models\Factura;
use App\Models\Tasa;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CuotasVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuotas:vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcular cuotas vencidas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ahora = Carbon::now();
        $ahora->setTime(0,0);
        $cuotas = Cuota::where(function($q){$q->where('estado', 'Vigente')->orWhere('estado', 'Vencida');})->whereHas('credito', function($q){$q->where('estado', 'En cobro');})->get();

        foreach ($cuotas as $cuota) {
            $vencimiento = Carbon::parse($cuota->fecha_vencimiento);
            if($vencimiento < $ahora && $cuota->descripcion == null){
                $cuota->estado = "Vencida";
                $cuota->mora = $ahora->diffInDays($vencimiento);
                if($cuota->estado_mora == 1){
                    $vencimiento = Carbon::parse($cuota->fecha_mora);
                    $vencimiento->addDay(); 
                    $dias = 1;
                    $total = 0;
                    $mes = $vencimiento->month;
                    $year = $vencimiento->year;
                    while($vencimiento < $ahora){  
                        $vencimiento->addDay();
                        if($vencimiento->month != $mes){
                            $tasa = Tasa::where('tipo', 'Mora')->where('year', $year)->where('mes', $mes)->first();
                            $total = $total + (($cuota->saldo_capital * ($tasa->valor/100)) / 360) * $dias;
                            $dias = 1;
                            $mes = $vencimiento->month;
                            $year = $vencimiento->year;
                        }else{
                            $dias++;
                        }   
                    }
                    if($dias > 0){
                        $tasa = Tasa::where('tipo', 'Mora')->where('year', $year)->where('mes', $mes)->first();
                        $total = $total + (($cuota->saldo_capital * ($tasa->valor/100)) / 360) * $dias;
                    }
                    $cuota->saldo_mora = $total;
                }
                $cuota->save();  
            }
        }

        $facturas = Factura::doesnthave('credito')->where('cruzada', 0)->whereNotNull('vencimiento')->get();
        foreach ($facturas as $factura) {
            $vencimiento = Carbon::parse($factura->vencimiento);
            if($vencimiento < $ahora){
                $vencimiento = Carbon::parse($factura->fecha_mora);
                $vencimiento->addDay(); 
                $dias = 1;
                $total = 0;
                $mes = $vencimiento->month;
                $year = $vencimiento->year;
                while($vencimiento < $ahora){  
                    $vencimiento->addDay();
                    if($vencimiento->month != $mes){
                        $tasa = Tasa::where('tipo', 'Mora')->where('year', $year)->where('mes', $mes)->first();
                        $total = $total + (($factura->saldo * ($tasa->valor/100)) / 360) * $dias;
                        $dias = 1;
                        $mes = $vencimiento->month;
                        $year = $vencimiento->year;
                    }else{
                        $dias++;
                    }   
                }
                if($dias > 0){
                    $tasa = Tasa::where('tipo', 'Mora')->where('year', $year)->where('mes', $mes)->first();
                    $total = $total + (($factura->saldo * ($tasa->valor/100)) / 360) * $dias;
                }
                $factura->mora = $total;
                $factura->save();
            }   
        }
    }
}
