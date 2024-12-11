<?php

namespace App\Console\Commands;

use App\Models\Credito;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotificacionesVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificar vencidas';

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
        $diferencia = Carbon::now()->subDay()->format("Y-m-d");
        $creditos = Credito::with('cuotas', 'cliente')->where('estado', 'En cobro')->whereHas('cuotas', function($q) use($diferencia){$q->Where('estado', 'Vencida')->whereDate('fecha_vencimiento', $diferencia);})->get();
        foreach ($creditos as $credito) {
            $vencidas = [];
            foreach ($credito->cuotas as $cuota) {
                if($cuota->estado == "Vencida"){
                    $vencidas[] = $cuota->ncuota;
                }
            }
            
            if(count($vencidas) > 1){
                $texto = "Hola " . $credito->cliente->primer_nombre . ", queremos recordarte que las cuotas " . implode(",", $vencidas) . " de " .
                count($credito->cuotas) . " para tu crédito con Cahors se encuentran vencidas. Te invitamos a cancelarlas y evitar reportes en las centrales. Feliz día.";
            }elseif (count($vencidas) == 1) {
                $texto = "Hola " . $credito->cliente->primer_nombre . ", olvidaste el pago de tu cuota " . $vencidas[0] . " de " .
                count($credito->cuotas) . " que venció ayer de tu crédito con Cahors. Te invitamos a cancelarla y evitar cobros por mora. Feliz día.";
            }          
            $this->enviarSMS($texto, $credito->cliente->celular);        
        }


        $diferencia = Carbon::now()->subDays(5)->format("Y-m-d");
        $creditos = Credito::with('cuotas', 'cliente')->where('estado', 'En cobro')->whereHas('cuotas', function($q) use($diferencia){$q->Where('estado', 'Vencida')->whereDate('fecha_vencimiento', $diferencia);})->get();
        foreach ($creditos as $credito) {
            $vencidas = [];
            foreach ($credito->cuotas as $cuota) {
                if($cuota->estado == "Vencida"){
                    $vencidas[] = $cuota->ncuota;
                }
            }
            
            if(count($vencidas) > 1){
                $texto = "Hola " . $credito->cliente->primer_nombre . ", queremos recordarte que las cuotas " . implode(",", $vencidas) . " de " .
                count($credito->cuotas) . " para tu crédito con Cahors se encuentran vencidas. Te invitamos a cancelarlas y evitar reportes en las centrales. Feliz día.";
            }elseif (count($vencidas) == 1) {
                $texto = "Hola " . $credito->cliente->primer_nombre . ", olvidaste el pago de tu cuota " . $vencidas[0] . " de " .
                count($credito->cuotas) . " que venció hace 5 días de tu crédito con Cahors. Te invitamos a cancelarla y evitar cobros por mora. Feliz día.";
            }  
            $this->enviarSMS($texto, $credito->cliente->celular);
        }
    }


    public function enviarSMS($texto, $numero)
    {
        $connection = fopen('https://portal.bulkgate.com/api/1.0/simple/transactional', 'r', false,
        stream_context_create(['http' => [
            'method' => 'POST',
            'header' => [
                'Content-type: application/json'
            ],
            'content' => json_encode([
                'application_id' => '22484',
                'application_token' => '8f1lnuLJwXRwXFzGnwbqbGw8p1WtQZ39U2lqwYLJG46pNLo6Ct',
                'number' => $numero,
                'text' => $texto,
            ]),
            'ignore_errors' => true
            ]])
        );

        $logFile = fopen(storage_path() . DIRECTORY_SEPARATOR . "vencidasSMS.txt", 'a') or die("Error creando archivo");

        if($connection)
        {
            //$response = json_decode(stream_get_contents($connection));        
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). stream_get_contents($connection)) or die("Error escribiendo en el archivo");
            fclose($connection);
        }else{
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). "Falla conexión: " . $numero) or die("Error escribiendo en el archivo");
        }
        fclose($logFile);
    }
}
