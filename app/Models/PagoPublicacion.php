<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoPublicacion extends Model
{
    protected $table = 'pagos_publicacion';

    protected $fillable = [
        'proyecto_id',
        'empresa_nit',
        'tipo',
        'monto',
        'moneda',
        'metodo_pago',
        'referencia_pago',
        'mercadopago_id',
        'estado',
        'fecha_pago',
        'comprobante_url',
        'mercadopago_response',
        'confirmado_por',
        'nequi_phone',
        'nequi_transaction_id',
        'nequi_response',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'mercadopago_response' => 'json',
        'nequi_response' => 'json',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id', 'id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_nit', 'nit');
    }

    public function confirmador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por', 'id');
    }

    public function isConfirmado(): bool
    {
        return $this->estado === 'confirmado';
    }

    public function isPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function isRechazado(): bool
    {
        return $this->estado === 'rechazado';
    }
}
