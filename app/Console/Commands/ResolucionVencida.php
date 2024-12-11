<?php

namespace App\Console\Commands;

use App\Models\Resolucion;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ResolucionVencida extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resolucion:alerta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificar resoluciones próximas a vencer';

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
        $resoluciones = Resolucion::get();
        $ahora = Carbon::now();
        $enviarCorreo = false;
        foreach ($resoluciones as $resolucion) {
            $resolucion->alertar = false;
            $diferencia = $ahora->diffInDays($resolucion->fechafi, false);
            if($diferencia == 2 || $diferencia == 0){
                $resolucion->alertar = true;
                $enviarCorreo = true;
            }
        }

        if($enviarCorreo){
            $this->enviarCorreo($resoluciones);
        }
    }

    function enviarCorreo($resoluciones){
        try{
            Mail::send('notificaciones.emailResoluciones', compact('resoluciones'), function ($message) {
                $message->from("EMAILNOTIFY", "JADMIN");
                $message->to(["EMAILDESTINATARY1", "EMAILDETINATARY2"]);
                $message->subject("Notificación Resoluciones JADMIN");
            
            });
        }
        catch(Exception $ex){
            $logFile = fopen(storage_path('informecentral') . '/logCorreoInfTransunion.txt', 'a');
            fwrite($logFile, "\n".date("d/m/Y H:i:s") . "-" . $ex->getMessage() . "---" . $ex->getLine());
            fclose($logFile);
        }
    }
}
