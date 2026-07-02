<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NequiService
{
    protected string $mode;
    protected string $accessKeyId;
    protected string $secretKey;
    protected string $apiKey;
    protected string $baseUrl;
    protected string $merchantPhone;

    public function __construct()
    {
        $this->mode = config('nequi.mode', 'sandbox');
        $this->accessKeyId = config('nequi.access_key_id', '');
        $this->secretKey = config('nequi.secret_key', '');
        $this->apiKey = config('nequi.api_key', '');
        $this->baseUrl = config('nequi.base_url', 'https://apiqa.nequi.com.co');
        $this->merchantPhone = config('nequi.phone_number', '3000000000');
    }

    /**
     * Enviar solicitud de pago vía notificación push Nequi
     *
     * @param string $payerPhone Número del pagador
     * @param int $amount Monto en COP
     * @param string $description Descripción del pago
     * @return array{success: bool, transaction_id?: string, qr_code?: string, message?: string, error?: string}
     */
    public function sendPaymentRequest(string $payerPhone, int $amount, string $description): array
    {
        if ($this->mode === 'sandbox') {
            return $this->simulatePayment($payerPhone, $amount, $description);
        }

        return $this->sendRealPaymentRequest($payerPhone, $amount, $description);
    }

    /**
     * Consultar estado de una transacción Nequi
     */
    public function checkPaymentStatus(string $transactionId): array
    {
        if ($this->mode === 'sandbox') {
            return [
                'success' => true,
                'status' => 'APPROVED',
                'transaction_id' => $transactionId,
                'message' => 'Pago aprobado (sandbox)',
            ];
        }

        return $this->checkRealPaymentStatus($transactionId);
    }

    /**
     * Verificar firma del webhook de Nequi
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        if ($this->mode === 'sandbox') {
            return true;
        }

        $expected = hash_hmac('sha256', json_encode($payload), $this->secretKey);
        return hash_equals($expected, $signature);
    }

    // ─────────────────────────────────────────────
    //  Sandbox / Simulación
    // ─────────────────────────────────────────────

    protected function simulatePayment(string $payerPhone, int $amount, string $description): array
    {
        $transactionId = 'NEQUI_SB_' . strtoupper(Str::random(16));

        Log::info('[Nequi Sandbox] Simulando pago', [
            'payer' => $payerPhone,
            'amount' => $amount,
            'transaction_id' => $transactionId,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Notificación push enviada (sandbox). Revisa la app Nequi de pruebas.',
            'sandbox' => true,
        ];
    }

    // ─────────────────────────────────────────────
    //  Producción — API real Nequi Conecta
    // ─────────────────────────────────────────────

    protected function sendRealPaymentRequest(string $payerPhone, int $amount, string $description): array
    {
        try {
            $endpoint = $this->baseUrl . config('nequi.push.endpoint');

            $messageId = 'BPS-' . Str::uuid()->toString();

            $body = [
                'phoneNumber' => $this->formatPhone($payerPhone),
                'amount' => $amount,
                'messageId' => $messageId,
                'description' => Str::limit($description, 100),
                'webhookUrl' => config('nequi.webhook_url'),
            ];

            $response = Http::withHeaders($this->buildHeaders())
                ->withBody(json_encode($body), 'application/json')
                ->post($endpoint, $body);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transaction_id' => $data['transactionId'] ?? $messageId,
                    'message' => $data['message'] ?? 'Notificación enviada',
                ];
            }

            Log::error('[Nequi API] Error en solicitud de pago', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Error al comunicarse con Nequi: ' . ($response->body() ?: 'Error desconocido'),
            ];
        } catch (\Exception $e) {
            Log::error('[Nequi API] Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error de conexión con Nequi: ' . $e->getMessage(),
            ];
        }
    }

    protected function checkRealPaymentStatus(string $transactionId): array
    {
        try {
            $endpoint = str_replace(
                '{transactionId}',
                $transactionId,
                $this->baseUrl . config('nequi.push.status_endpoint')
            );

            $response = Http::withHeaders($this->buildHeaders())->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'PENDING',
                    'transaction_id' => $transactionId,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => 'Error consultando estado: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ─────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────

    protected function buildHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
            'X-Request-ID' => (string) Str::uuid(),
        ];

        if ($this->accessKeyId && $this->secretKey) {
            $headers['X-Access-Key-Id'] = $this->accessKeyId;
            $headers['Authorization'] = $this->buildAwsSignature();
        }

        return $headers;
    }

    protected function buildAwsSignature(): string
    {
        $service = 'nequi';
        $region = 'us-east-1';
        $now = now()->utc();
        $date = $now->format('Ymd');
        $dateTime = $now->format('Ymd\THis\Z');

        $signedHeaders = 'host;x-amz-date';
        $payloadHash = hash('sha256', '');

        $canonicalRequest = "POST\n/\n\nhost:" . parse_url($this->baseUrl, PHP_URL_HOST) . "\nx-amz-date:$dateTime\n\n$signedHeaders\n$payloadHash";
        $credentialScope = "$date/$region/$service/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n$dateTime\n$credentialScope\n" . hash('sha256', $canonicalRequest);

        $dateKey = hash_hmac('sha256', $date, "AWS4{$this->secretKey}", true);
        $regionKey = hash_hmac('sha256', $region, $dateKey, true);
        $serviceKey = hash_hmac('sha256', $service, $regionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $serviceKey, true);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/{$credentialScope}, SignedHeaders=$signedHeaders, Signature=$signature";
    }

    protected function formatPhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    public function isSandbox(): bool
    {
        return $this->mode === 'sandbox';
    }

    public function getMerchantPhone(): string
    {
        return $this->merchantPhone;
    }
}
