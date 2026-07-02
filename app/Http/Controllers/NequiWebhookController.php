<?php

namespace App\Http\Controllers;

use App\Models\PagoPublicacion;
use App\Models\Proyecto;
use App\Services\NequiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NequiWebhookController extends Controller
{
    protected NequiService $nequi;

    public function __construct(NequiService $nequi)
    {
        $this->nequi = $nequi;
    }

    /**
     * Webhook llamado por Nequi cuando cambia el estado de un pago
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('X-Nequi-Signature', '');

        Log::info('[Nequi Webhook] Recibido', $payload);

        if (!$this->nequi->verifyWebhookSignature($payload, $signature)) {
            Log::warning('[Nequi Webhook] Firma inválida');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $transactionId = $payload['transactionId'] ?? $payload['transaction_id'] ?? null;
        $status = $payload['status'] ?? $payload['paymentStatus'] ?? null;
        $phone = $payload['phoneNumber'] ?? $payload['phone_number'] ?? null;

        if (!$transactionId || !$status) {
            return response()->json(['error' => 'Missing required fields'], 422);
        }

        $pago = PagoPublicacion::where('nequi_transaction_id', $transactionId)->first();
        if (!$pago) {
            Log::warning("[Nequi Webhook] Transacción {$transactionId} no encontrada");
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        DB::beginTransaction();
        try {
            $pago->update([
                'nequi_response' => $payload,
                'estado' => $this->mapStatus($status),
            ]);

            if ($status === 'APPROVED' || $status === 'SUCCESS') {
                $proyecto = Proyecto::find($pago->proyecto_id);
                if ($proyecto) {
                    app(PagoController::class)->activarPlan($proyecto, $pago);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Nequi Webhook] Error procesando: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Endpoint para simular aprobación de pago Nequi (solo sandbox)
     */
    public function simulateApprove(Request $request): JsonResponse
    {
        $transactionId = $request->input('transaction_id');
        $action = $request->input('action', 'approve');

        if (!$transactionId) {
            return response()->json(['error' => 'transaction_id required'], 422);
        }

        $pago = PagoPublicacion::where('nequi_transaction_id', $transactionId)->first();
        if (!$pago) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $pago->update([
                    'estado' => 'confirmado',
                    'nequi_response' => ['simulated' => true, 'status' => 'APPROVED', 'transaction_id' => $transactionId],
                ]);

                $proyecto = Proyecto::find($pago->proyecto_id);
                if ($proyecto) {
                    app(PagoController::class)->activarPlan($proyecto, $pago);
                }

                DB::commit();
                return response()->json(['message' => 'Pago simulado APROBADO', 'status' => 'approved']);
            }

            $pago->update([
                'estado' => 'rechazado',
                'nequi_response' => ['simulated' => true, 'status' => 'REJECTED', 'transaction_id' => $transactionId],
            ]);
            DB::commit();
            return response()->json(['message' => 'Pago simulado RECHAZADO', 'status' => 'rejected']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'APPROVED', 'SUCCESS', 'CONFIRMED' => 'confirmado',
            'REJECTED', 'CANCELED', 'REFUSED', 'FAILED' => 'rechazado',
            'PENDING', 'PROCESSING' => 'pendiente',
            default => 'pendiente',
        };
    }
}
