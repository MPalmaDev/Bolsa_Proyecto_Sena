<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WompiService
{
    protected string $mode;
    protected string $publicKey;
    protected string $privateKey;
    protected string $integritySecret;
    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->mode = config('wompi.mode', 'sandbox');
        $this->publicKey = config('wompi.public_key', '');
        $this->privateKey = config('wompi.private_key', '');
        $this->integritySecret = config('wompi.integrity_secret', '');
        $this->apiBaseUrl = config("wompi.api.{$this->mode}");
    }

    /**
     * Genera la firma de integridad SHA256 para el widget
     */
    public function generateIntegritySignature(string $reference, int $amountInCents, string $currency = 'COP'): string
    {
        $concatenated = "{$reference}{$amountInCents}{$currency}{$this->integritySecret}";
        return hash('sha256', $concatenated);
    }

    /**
     * Genera una referencia única de pago
     */
    public function generateReference(): string
    {
        return 'BPS-' . strtoupper(Str::random(12)) . '-' . time();
    }

    /**
     * Consulta el estado de una transacción vía API Wompi
     */
    public function getTransactionStatus(string $transactionId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
            ])->get("{$this->apiBaseUrl}/transactions/{$transactionId}");

            if ($response->successful()) {
                $data = $response->json('data', $response->json());
                return [
                    'success' => true,
                    'status' => $data['status'] ?? null,
                    'status_message' => $data['status_message'] ?? null,
                    'id' => $data['id'] ?? $transactionId,
                    'amount_in_cents' => $data['amount_in_cents'] ?? 0,
                    'reference' => $data['reference'] ?? null,
                    'payment_method' => $data['payment_method'] ?? null,
                ];
            }

            Log::warning('[Wompi] Error consultando transacción', [
                'id' => $transactionId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('[Wompi] Excepción consultando transacción: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica la firma de integridad del webhook
     * Wompi envía la firma en el header X-Wompi-Signature
     * Firma = SHA256(<id><status><amount_in_cents><currency><integrity_secret>)
     */
    public function verifyWebhookSignature(array $payload, string $receivedSignature): bool
    {
        if ($this->mode === 'sandbox') {
            return true;
        }

        try {
            $id = $payload['data']['transaction']['id'] ?? '';
            $status = $payload['data']['transaction']['status'] ?? '';
            $amount = $payload['data']['transaction']['amount_in_cents'] ?? '';
            $currency = $payload['data']['transaction']['currency'] ?? '';

            $concatenated = "{$id}{$status}{$amount}{$currency}{$this->integritySecret}";
            $expected = hash('sha256', $concatenated);

            return hash_equals($expected, $receivedSignature);
        } catch (\Exception $e) {
            Log::error('[Wompi] Error verificando firma: ' . $e->getMessage());
            return false;
        }
    }

    public function isSandbox(): bool
    {
        return $this->mode === 'sandbox';
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function hasKeys(): bool
    {
        return !empty($this->publicKey) && !empty($this->integritySecret);
    }
}
