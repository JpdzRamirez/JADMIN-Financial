<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\AutFactura',
        'App\Console\Commands\CuotasVencidas',
        'App\Console\Commands\CuotasVigentes',
        'App\Console\Commands\NotificacionesVencidas',
        'App\Console\Commands\NotificacionesVigentes',
        'App\Console\Commands\GenerarInformeTransunion',
        'App\Console\Commands\ResolucionVencida'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cuotas:vigentes')->dailyAt('00:01');
        $schedule->command('cuotas:vencidas')->dailyAt('00:05');
        $schedule->command('informetransunion:generar')->monthlyOn(1, '04:00');
        $schedule->command('resolucion:alerta')->daily();
        $schedule->command('autofactura:enviar')->daily();
       
        //$schedule->command('notificaciones:vigentes')->dailyAt('12:00');
        //$schedule->command('notificaciones:vencidas')->dailyAt('12:05');   
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
