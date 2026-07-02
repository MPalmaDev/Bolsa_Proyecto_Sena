<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_publicacion', function (Blueprint $table) {
            $table->string('nequi_phone', 20)->nullable()->after('mercadopago_response')
                  ->comment('Número Nequi del pagador');
            $table->string('nequi_transaction_id', 100)->nullable()->after('nequi_phone')
                  ->comment('ID de transacción devuelto por Nequi');
            $table->json('nequi_response')->nullable()->after('nequi_transaction_id')
                  ->comment('Respuesta completa de la API Nequi');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_publicacion', function (Blueprint $table) {
            $table->dropColumn(['nequi_phone', 'nequi_transaction_id', 'nequi_response']);
        });
    }
};
