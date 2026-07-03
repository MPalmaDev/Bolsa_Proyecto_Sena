<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PagoPublicacion;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Aprendiz;
use App\Notifications\AppNotification;
use App\Services\WompiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

class PagoController extends Controller
{
    protected WompiService $wompi;

    public function __construct(WompiService $wompi)
    {
        $this->wompi = $wompi;
        if (config('mercadopago.access_token')) {
            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
            MercadoPagoConfig::setRuntimeEnviroment(config('mercadopago.sandbox') ? 'sandbox' : 'production');
        }
    }

    public function mostrarPago(Request $request, int $proyectoId): View|RedirectResponse
    {
        $nit = session('nit');
        $proyecto = Proyecto::where('id', $proyectoId)
            ->where('empresa_nit', $nit)
            ->firstOrFail();

        if ($proyecto->tipo_publicacion !== 'gratuito') {
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('info', 'Este proyecto ya tiene un plan de visibilidad activo.');
        }

        $tipo = $request->query('tipo', 'destacado');
        if (!in_array($tipo, ['destacado', 'patrocinado'])) {
            $tipo = 'destacado';
        }

        $precios = config('app_config.publicacion');
        $precio = $precios[$tipo]['precio'] ?? 0;
        $dias = $precios[$tipo]['dias'] ?? 7;

        return view('empresa.pago', compact('proyecto', 'tipo', 'precio', 'dias'));
    }

    public function procesarPago(Request $request, int $proyectoId): RedirectResponse
    {
        $usrId = session('usr_id');
        $nit = session('nit');

        $validated = $request->validate([
            'tipo' => 'required|in:destacado,patrocinado',
            'metodo' => 'required|in:mercadopago,wompi,manual',
            'comprobante' => 'required_if:metodo,manual|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'mercadopago_token' => 'nullable|string',
            'payment_method_id' => 'nullable|string',
            'installments' => 'nullable|integer|min:1',
        ]);

        $proyecto = Proyecto::where('id', $proyectoId)
            ->where('empresa_nit', $nit)
            ->firstOrFail();

        if ($proyecto->tipo_publicacion !== 'gratuito') {
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('info', 'Ya tiene un plan activo.');
        }

        $precios = config('app_config.publicacion');
        $tipo = $validated['tipo'];
        $precio = $precios[$tipo]['precio'];
        $dias = $precios[$tipo]['dias'];

        DB::beginTransaction();
        try {
            $pago = PagoPublicacion::create([
                'proyecto_id' => $proyecto->id,
                'empresa_nit' => $nit,
                'tipo' => $tipo,
                'monto' => $precio,
                'moneda' => 'COP',
                'metodo_pago' => $validated['metodo'],
                'estado' => 'pendiente',
            ]);

            if ($validated['metodo'] === 'manual') {
                $comprobanteUrl = null;
                if ($request->hasFile('comprobante')) {
                    $file = $request->file('comprobante');
                    $safeFilename = 'comprobante_' . $nit . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
                    $comprobanteUrl = $file->storeAs('comprobantes', $safeFilename, 'public');
                }
                $pago->update(['comprobante_url' => $comprobanteUrl]);

                $this->notificarAdminNuevoPago($proyecto, $pago);
                DB::commit();

                return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                    ->with('success', 'Solicitud de pago enviada. Recibira una confirmacion cuando el administrador valide el pago.');
            }

            if ($validated['metodo'] === 'mercadopago') {
                return $this->procesarMercadoPago($proyecto, $pago, $validated, $usrId);
            }

            if ($validated['metodo'] === 'wompi') {
                return $this->procesarWompi($proyecto, $pago);
            }

            DB::commit();
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('error', 'Metodo de pago no valido.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar pago: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('error', 'Error al procesar el pago. Intente nuevamente.');
        }
    }

    private function procesarMercadoPago(Proyecto $proyecto, PagoPublicacion $pago, array $validated, int $usrId): RedirectResponse
    {
        $empresa = Empresa::where('nit', session('nit'))->first();
        $usuario = User::find($usrId);

        try {
            $client = new PaymentClient();
            $paymentData = [
                'transaction_amount' => (float) $pago->monto,
                'token' => $validated['mercadopago_token'],
                'description' => "{$pago->tipo}: {$proyecto->titulo}",
                'installments' => $validated['installments'] ?? 1,
                'payment_method_id' => $validated['payment_method_id'],
                'payer' => [
                    'email' => $usuario?->correo ?? ($empresa?->correo_contacto ?? ''),
                    'identification' => [
                        'type' => 'NIT',
                        'number' => (string) session('nit'),
                    ],
                ],
            ];

            $payment = $client->create($paymentData);

            $pago->update([
                'mercadopago_id' => $payment->id,
                'mercadopago_response' => json_decode(json_encode($payment), true),
                'referencia_pago' => $payment->id,
            ]);

            if ($payment->status === 'approved') {
                $this->activarPlan($proyecto, $pago);
                DB::commit();

                return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                    ->with('success', 'Pago aprobado. Su proyecto ahora es ' . $pago->tipo . '.');
            }

            $pago->update(['estado' => 'rechazado']);
            DB::commit();

            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('error', 'El pago fue rechazado. Intente con otro metodo.');
        } catch (MPApiException $e) {
            DB::rollBack();
            Log::error('MercadoPago error: ' . $e->getMessage(), [
                'api_response' => $e->getApiResponse()->getContent(),
            ]);
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('error', 'Error en la pasarela de pago. Verifique sus datos e intente nuevamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error MercadoPago: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('error', 'Error al conectar con la pasarela de pago.');
        }
    }

    private function procesarWompi(Proyecto $proyecto, PagoPublicacion $pago): RedirectResponse
    {
        $reference = $this->wompi->generateReference();
        $amountInCents = (int) $pago->monto * 100;
        $signature = $this->wompi->generateIntegritySignature($reference, $amountInCents);

        $pago->update(['referencia_pago' => $reference]);

        DB::commit();

        $params = [
            'public-key' => $this->wompi->getPublicKey(),
            'currency' => 'COP',
            'amount-in-cents' => $amountInCents,
            'reference' => $reference,
            'signature:integrity' => $signature,
            'redirect-url' => config('wompi.return_url') . "?ref={$reference}",
        ];

        return redirect()->to(config('wompi.checkout_url') . '?' . http_build_query($params));
    }

    public function respuestaWompi(Request $request): View|RedirectResponse
    {
        $reference = $request->query('ref', '');
        $transactionId = $request->query('id', '');

        if (empty($reference)) {
            return redirect()->route('empresa.dashboard')
                ->with('error', 'Referencia de pago no encontrada.');
        }

        $pago = PagoPublicacion::where('referencia_pago', $reference)->first();
        if (!$pago) {
            return redirect()->route('empresa.dashboard')
                ->with('error', 'Pago no encontrado.');
        }

        $proyecto = Proyecto::find($pago->proyecto_id);

        if (!empty($transactionId)) {
            $txStatus = $this->wompi->getTransactionStatus($transactionId);

            if ($txStatus && $txStatus['success']) {
                $status = strtoupper($txStatus['status']);

                if ($status === 'APPROVED') {
                    DB::beginTransaction();
                    try {
                        $pago->update([
                            'estado' => 'confirmado',
                            'nequi_response' => $txStatus,
                        ]);
                        $this->activarPlan($proyecto, $pago);
                        DB::commit();

                        return view('empresa.wompi-respuesta', [
                            'pago' => $pago,
                            'proyecto' => $proyecto,
                            'exito' => true,
                            'mensaje' => 'Pago aprobado por Wompi. Su proyecto ahora es ' . $pago->tipo . '.',
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('[Wompi] Error activando plan: ' . $e->getMessage());
                    }
                } elseif (in_array($status, ['DECLINED', 'VOIDED', 'ERROR'])) {
                    $pago->update(['estado' => 'rechazado']);
                    return view('empresa.wompi-respuesta', [
                        'pago' => $pago,
                        'proyecto' => $proyecto,
                        'exito' => false,
                        'mensaje' => 'El pago no fue aprobado. Puede intentar con otro metodo.',
                    ]);
                }
            }
        }

        return view('empresa.wompi-respuesta', [
            'pago' => $pago,
            'proyecto' => $proyecto,
            'exito' => null,
            'mensaje' => 'Su pago esta siendo procesado. Recibira una notificacion cuando se confirme.',
        ]);
    }

    public function webhookWompi(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('X-Wompi-Signature', '');

        Log::info('[Wompi Webhook] Recibido', $payload);

        if (!$this->wompi->verifyWebhookSignature($payload, $signature)) {
            Log::warning('[Wompi Webhook] Firma invalida');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $transaction = $payload['data']['transaction'] ?? null;
        if (!$transaction) {
            return response()->json(['error' => 'No transaction data'], 422);
        }

        $reference = $transaction['reference'] ?? null;
        $status = strtoupper($transaction['status'] ?? '');

        if (!$reference || !$status) {
            return response()->json(['error' => 'Missing reference or status'], 422);
        }

        $pago = PagoPublicacion::where('referencia_pago', $reference)->first();
        if (!$pago) {
            return response()->json(['error' => 'Reference not found'], 404);
        }

        DB::beginTransaction();
        try {
            if ($status === 'APPROVED') {
                $pago->update([
                    'estado' => 'confirmado',
                    'nequi_response' => $payload,
                ]);

                $proyecto = Proyecto::find($pago->proyecto_id);
                if ($proyecto) {
                    $this->activarPlan($proyecto, $pago);
                }
            } elseif (in_array($status, ['DECLINED', 'VOIDED', 'ERROR'])) {
                $pago->update([
                    'estado' => 'rechazado',
                    'nequi_response' => $payload,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Wompi Webhook] Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }

        return response()->json(['message' => 'OK']);
    }

    public function activarPlan(Proyecto $proyecto, PagoPublicacion $pago): void
    {
        $precios = config('app_config.publicacion');
        $dias = $precios[$pago->tipo]['dias'] ?? 7;

        $proyecto->update([
            'tipo_publicacion' => $pago->tipo,
            'fecha_inicio_plan' => now(),
            'fecha_fin_plan' => now()->addDays($dias),
            'pago_id' => $pago->id,
        ]);

        $pago->update([
            'estado' => 'confirmado',
            'fecha_pago' => now(),
            'confirmado_por' => session('usr_id'),
        ]);

        $empresa = $proyecto->empresa;
        if ($empresa?->usuario) {
            $mensaje = $pago->tipo === 'patrocinado'
                ? "Su proyecto {$proyecto->titulo} ahora es Patrocinado y aparecera en la pagina principal."
                : "Su proyecto {$proyecto->titulo} ahora es Destacado en su categoria.";
            $empresa->usuario->notify(new AppNotification(
                'Plan activado: ' . ucfirst($pago->tipo),
                $mensaje,
                $pago->tipo === 'patrocinado' ? 'fa-crown' : 'fa-star',
                route('empresa.proyectos.detalle', $proyecto->id)
            ));
        }

        if ($pago->tipo === 'patrocinado') {
            $this->notificarAprendicesCategoria($proyecto);
        }
    }

    private function notificarAprendicesCategoria(Proyecto $proyecto): void
    {
        $categoria = $proyecto->categoria;
        $aprendices = Aprendiz::where('activo', true)
            ->where('programa_formacion', 'LIKE', "%{$categoria}%")
            ->with('usuario')
            ->get();

        foreach ($aprendices as $aprendiz) {
            if ($aprendiz->usuario) {
                try {
                    $aprendiz->usuario->notify(new AppNotification(
                        'Nuevo proyecto patrocinado',
                        "{$proyecto->titulo} - {$proyecto->empresa?->nombre}",
                        'fa-crown',
                        route('aprendiz.proyecto.detalle', $proyecto->id)
                    ));
                } catch (\Exception $e) {
                    Log::error("Error notificando aprendiz {$aprendiz->id}: " . $e->getMessage());
                }
            }
        }
    }

    private function notificarAdminNuevoPago(Proyecto $proyecto, PagoPublicacion $pago): void
    {
        $admins = User::where('rol_id', User::ROL_ADMIN)->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new AppNotification(
                    'Nuevo pago por visibilidad',
                    "{$proyecto->titulo} - Plan {$pago->tipo} - \${$pago->monto} COP",
                    'fa-credit-card',
                    route('admin.pagos')
                ));
            } catch (\Exception $e) {
                Log::error("Error notificando admin: " . $e->getMessage());
            }
        }
    }

    public function mostrarFormularioPago(int $proyectoId): View|RedirectResponse
    {
        $nit = session('nit');
        $proyecto = Proyecto::where('id', $proyectoId)
            ->where('empresa_nit', $nit)
            ->firstOrFail();

        if ($proyecto->tipo_publicacion !== 'gratuito') {
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('info', 'Este proyecto ya tiene un plan de visibilidad.');
        }

        $precios = config('app_config.publicacion');

        return view('empresa.seleccionar-plan', compact('proyecto', 'precios'));
    }
}
