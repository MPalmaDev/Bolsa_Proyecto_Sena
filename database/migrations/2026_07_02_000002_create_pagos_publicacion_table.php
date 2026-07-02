<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_publicacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proyecto_id');
            $table->unsignedBigInteger('empresa_nit');
            $table->enum('tipo', ['destacado', 'patrocinado']);
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('COP');
            $table->string('metodo_pago', 50)->nullable();
            $table->string('referencia_pago', 100)->nullable();
            $table->string('mercadopago_id', 100)->nullable();
            $table->enum('estado', ['pendiente', 'confirmado', 'rechazado', 'reembolsado'])->default('pendiente');
            $table->timestamp('fecha_pago')->nullable();
            $table->string('comprobante_url', 255)->nullable();
            $table->json('mercadopago_response')->nullable();
            $table->unsignedBigInteger('confirmado_por')->nullable();
            $table->timestamps();

            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreign('empresa_nit')->references('nit')->on('empresas')->onDelete('cascade');
            $table->foreign('confirmado_por')->references('id')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_publicacion');
    }
};
