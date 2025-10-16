<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        // Aviso 7 días ANTES de que ABRA la ventana
        $schedule->command('verificacion:notificar-apertura --dias=7 --role=administrador')
            ->dailyAt('10:00')
            ->timezone('America/Mexico_City');

        // Aviso 7 días ANTES de que CIERRE la ventana
        $schedule->command('verificacion:notificar-cierre --dias=7 --role=administrador')
            ->dailyAt('10:02')
            ->timezone('America/Mexico_City');
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

