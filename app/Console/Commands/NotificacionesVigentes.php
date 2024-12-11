<?php

namespace App\Console\Commands;

use App\Models\Credito;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotificacionesVigentes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:vigentes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificar vigentes';

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
        $diferencia = Carbon::now()->addDays(5)->format("Y-m-d");
        $creditos = Credito::with('cuotas', 'cliente')->where('estado', 'En cobro')->whereHas('cuotas', function($q) use($diferencia){$q->where('estado', 'Vigente')->whereDate('fecha_vencimiento', $diferencia);})->get();
        foreach ($creditos as $credito) {
            $texto = "";
            foreach ($credito->cuotas as $cuota) {
                if($cuota->estado == "Vigente"){
                    $texto = "Hola " . $credito->cliente->primer_nombre . ", En Cahors queremos recordarte que la fecha límite de pago para su cuota " . $cuota->ncuota . " de " .
                    count($credito->cuotas) . " es " . $cuota->fecha_vencimiento . " .Vence en 5 días. Feliz día.";
                    break;
                }
            }          
            $this->enviarSMS($texto, $credito->cliente->celular);
        }

        $diferencia = Carbon::now()->addDays(1)->format("Y-m-d");
        $creditos = Credito::with('cuotas', 'cliente')->where('estado', 'En cobro')->whereHas('cuotas', function($q) use($diferencia){$q->where('estado', 'Vigente')->whereDate('fecha_vencimiento', $diferencia);})->get();
        foreach ($creditos as $credito) {
            $texto = "";
            foreach ($credito->cuotas as $cuota) {
                if($cuota->estado == "Vigente"){
                    $texto = "Hola " . $credito->cliente->primer_nombre . ", En Cahors queremos recordarte que la fecha límite de pago para su cuota " . $cuota->ncuota . " de " .
                    count($credito->cuotas) . " es mañana " . $cuota->fecha_vencimiento . " Feliz día.";
                    break;
                }
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

        $logFile = fopen(storage_path() . DIRECTORY_SEPARATOR . "vigentesSMS.txt", 'a') or die("Error creando archivo");

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
