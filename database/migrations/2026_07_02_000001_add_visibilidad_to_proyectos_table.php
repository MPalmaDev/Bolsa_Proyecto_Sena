<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->enum('tipo_publicacion', ['gratuito', 'destacado', 'patrocinado'])
                ->default('gratuito')
                ->after('oferta_otro');
            $table->timestamp('fecha_inicio_plan')->nullable()->after('tipo_publicacion');
            $table->timestamp('fecha_fin_plan')->nullable()->after('fecha_inicio_plan');
            $table->unsignedBigInteger('pago_id')->nullable()->after('fecha_fin_plan');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['tipo_publicacion', 'fecha_inicio_plan', 'fecha_fin_plan', 'pago_id']);
        });
    }
};
