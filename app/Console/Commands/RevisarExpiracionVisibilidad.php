<?php

namespace App\Console\Commands;

use App\Models\Proyecto;
use Illuminate\Console\Command;

class RevisarExpiracionVisibilidad extends Command
{
    protected $signature = 'proyectos:revisar-expiracion-visibilidad';
    protected $description = 'Revoca la visibilidad destacada/patrocinada de proyectos cuyo plan haya expirado';

    public function handle(): int
    {
        $actualizados = Proyecto::whereIn('tipo_publicacion', ['destacado', 'patrocinado'])
            ->where('fecha_fin_plan', '<=', now())
            ->update([
                'tipo_publicacion' => 'gratuito',
                'fecha_inicio_plan' => null,
                'fecha_fin_plan' => null,
            ]);

        $this->info("Se revirtieron {$actualizados} proyectos a publicacion gratuita.");

        return Command::SUCCESS;
    }
}
