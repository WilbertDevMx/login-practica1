<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Kernel de consola de la aplicación.
 *
 * Define la programación de comandos y registra los comandos disponibles.
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define la programación de comandos de la aplicación.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Registra los comandos para la aplicación.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}