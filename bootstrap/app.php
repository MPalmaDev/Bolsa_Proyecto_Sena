<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // === RESPALDO ===
        // Backup automático cada 1.5 semanas (cada 10 días a las 2:00 AM)
        $schedule->command('backup:automatico')
                 ->cron('0 2 */10 * *')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/backup-automatico.log'));

        // === SEGURIDAD ===
        // Auditoría automática cada lunes a las 8:00 AM
        $schedule->command('guard:probe --send-email=bolsadeproyectossena@gmail.com')
                 ->mondays()
                 ->at('08:00')
                 ->withoutOverlapping();

        // === MANTENIMIENTO ===
        // Limpieza de datos caducados (tokens, notificaciones, sesiones, logs) - diario 3:00 AM
        $schedule->command('app:cleanup-expired')
                 ->dailyAt('03:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/cleanup-expired.log'));

        // Optimización de tablas de BD - domingos 4:00 AM
        $schedule->command('app:optimize-database')
                 ->weeklyOn(0, '04:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/optimize-database.log'));

        // === COLAS ===
        // Monitoreo y recuperación automática de jobs fallidos - cada 30 minutos
        $schedule->command('queue:auto-recover --pending-threshold=50')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/queue-auto-recover.log'));

        // Monitoreo de salud de colas - cada hora
        $schedule->command('queue:monitor')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/queue-monitor.log'));

        // === MONETIZACIÓN ===
        // Revocar visibilidad de proyectos con plan expirado - cada hora
        $schedule->command('proyectos:revisar-expiracion-visibilidad')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/visibilidad-expiracion.log'));
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\AuthMiddleware::class,
            'rol'         => \App\Http\Middleware\RolMiddleware::class,
            'ownership'   => \App\Http\Middleware\OwnershipMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

