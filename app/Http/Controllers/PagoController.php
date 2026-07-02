<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PagoPublicacion;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Aprendiz;
use App\Notifications\AppNotification;
use App\Services\NequiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class PagoController extends Controller
{
    protected NequiService $nequi;

    public function __construct(NequiService $nequi)
    {
        $this->nequi = $nequi;
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

        $nequiSandbox = $this->nequi->isSandbox();
        $nequiPhone = $this->nequi->getMerchantPhone();

        return view('empresa.pago', compact('proyecto', 'tipo', 'precio', 'dias', 'nequiSandbox', 'nequiPhone'));
    }

    public function procesarPago(Request $request, int $proyectoId): RedirectResponse
    {
        $usrId = session('usr_id');
        $nit = session('nit');

        $validated = $request->validate([
            'tipo' => 'required|in:destacado,patrocinado',
            'metodo' => 'required|in:mercadopago,manual,nequi',
            'comprobante' => 'required_if:metodo,manual|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'mercadopago_token' => 'required_if:metodo,mercadopago|string',
            'payment_method_id' => 'required_if:metodo,mercadopago|string',
            'installments' => 'nullable|integer|min:1',
            'nequi_phone' => 'required_if:metodo,nequi|string|min:7|max:20',
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

            if ($validated['metodo'] === 'nequi') {
                return $this->procesarNequi($proyecto, $pago, $validated, $usrId);
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

    private function procesarNequi(Proyecto $proyecto, PagoPublicacion $pago, array $validated, int $usrId): RedirectResponse
    {
        $phone = preg_replace('/[^0-9]/', '', $validated['nequi_phone']);

        $result = $this->nequi->sendPaymentRequest(
            $phone,
            (int) $pago->monto,
            config('nequi.push.description') . " - {$proyecto->titulo}"
        );

        if (!$result['success']) {
            DB::rollBack();
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('error', $result['error'] ?? 'Error al conectar con Nequi');
        }

        $pago->update([
            'nequi_phone' => $phone,
            'nequi_transaction_id' => $result['transaction_id'],
            'nequi_response' => $result,
        ]);

        DB::commit();

        if ($this->nequi->isSandbox()) {
            return redirect()->route('empresa.proyectos.nequi.simular', [
                'proyectoId' => $proyecto->id,
                'pagoId' => $pago->id,
                'transaction_id' => $result['transaction_id'],
            ])->with('success', 'Notificación push enviada a tu Nequi (sandbox).
                Usa la app Nequi de pruebas o haz clic en "Simular pago aprobado" para continuar.');
        }

        return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
            ->with('success', 'Revisa tu app Nequi. Te llegó una notificación push para aprobar el pago.');
    }

    public function mostrarSimulacionNequi(int $proyectoId, int $pagoId, string $transactionId): View|RedirectResponse
    {
        $nit = session('nit');
        $proyecto = Proyecto::where('id', $proyectoId)->where('empresa_nit', $nit)->firstOrFail();
        $pago = PagoPublicacion::where('id', $pagoId)
            ->where('proyecto_id', $proyectoId)
            ->where('empresa_nit', $nit)
            ->where('nequi_transaction_id', $transactionId)
            ->firstOrFail();

        if ($pago->estado !== 'pendiente') {
            return redirect()->route('empresa.proyectos.detalle', $proyecto->id)
                ->with('info', 'Este pago ya fue procesado.');
        }

        return view('empresa.nequi-simulacion', compact('pago', 'proyecto'));
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
