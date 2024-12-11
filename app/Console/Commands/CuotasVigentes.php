<?php

namespace App\Console\Commands;

use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Resolucion;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CuotasVigentes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuotas:vigentes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cuotas vigentes';

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
        //$cuotas = Cuota::where('estado', 'Pendiente')->whereHas('credito', function($q){$q->where('estado', 'En cobro');})->get();
        $creditos = Credito::with(['cuotas'=>function($q){$q->where('estado', 'Pendiente');}])->whereHas('cuotas', function($q){$q->where('estado', 'Pendiente');})->where('estado', 'En cobro')->get();
        $ahora = Carbon::now();
        $ahora->setTime(0,0);

        foreach ($creditos as $credito) {
            foreach ($credito->cuotas as $cuota) {
                $vencimiento = Carbon::parse($cuota->fecha_vencimiento);
                $vencimiento->subMonth();
                if($vencimiento == $ahora){
                    $cuota->estado = "Vigente";
                    $cuota->save();
                }
            }
        }
    }
}
