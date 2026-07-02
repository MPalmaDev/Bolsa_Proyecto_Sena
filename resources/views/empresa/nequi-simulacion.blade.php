@extends('layouts.dashboard')
@section('title', 'Pago con Nequi')
@section('page-title', 'Pago con Nequi')

@section('sidebar-nav')
    <span class="nav-label">Portal Empresa</span>
    <a href="{{ route('empresa.dashboard') }}" class="nav-item">
        <i class="fas fa-th-large"></i> Principal
    </a>
    <a href="{{ route('empresa.proyectos') }}" class="nav-item active">
        <i class="fas fa-project-diagram"></i> Mis Proyectos
    </a>
    <a href="{{ route('empresa.perfil') }}" class="nav-item">
        <i class="fas fa-building"></i> Perfil Empresa
    </a>
@endsection

@section('content')
<div style="max-width: 640px; margin: 0 auto; padding-bottom: 40px;">
    <div class="glass-card" style="padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #e84393, #6c5ce7); padding: 40px 48px; text-align: center;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <i class="fas fa-mobile-alt" style="font-size: 32px; color: white;"></i>
            </div>
            <h2 style="color: white; font-size: 24px; font-weight: 900;">Pago con Nequi</h2>
            <p style="color: rgba(255,255,255,0.7); font-size: 14px;">Modo sandbox — simulación</p>
        </div>

        <div style="padding: 40px 48px; text-align: center;">
            <div style="width: 120px; height: 120px; border-radius: 50%; background: #f0fdf4; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                <i class="fas fa-check-circle" style="font-size: 48px; color: #22c55e;"></i>
            </div>

            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px;">Notificación enviada</h3>
            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 24px;">
                Se envió una notificación push al Nequi <strong>{{ $pago->nequi_phone }}</strong>
                para aprobar el pago de <strong>${{ number_format($pago->monto) }} COP</strong>.
            </p>

            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 16px; padding: 20px; margin-bottom: 24px; text-align: left;">
                <p style="font-size: 13px; font-weight: 600; color: #92400e; margin-bottom: 8px;">
                    <i class="fas fa-flask"></i> Sandbox activo
                </p>
                <p style="font-size: 13px; color: #92400e;">
                    En producción el usuario recibe una notificación en su app Nequi. Aquí puedes simular la respuesta:
                </p>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <form method="POST" action="{{ route('empresa.proyectos.nequi.simular.procesar') }}">
                    @csrf
                    <input type="hidden" name="pago_id" value="{{ $pago->id }}">
                    <input type="hidden" name="transaction_id" value="{{ $pago->nequi_transaction_id }}">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn-premium" style="padding: 14px 32px; font-size: 15px; background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <i class="fas fa-check"></i> Simular pago APROBADO
                    </button>
                </form>
                <form method="POST" action="{{ route('empresa.proyectos.nequi.simular.procesar') }}">
                    @csrf
                    <input type="hidden" name="pago_id" value="{{ $pago->id }}">
                    <input type="hidden" name="transaction_id" value="{{ $pago->nequi_transaction_id }}">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" style="padding: 14px 32px; font-size: 15px; background: #f1f5f9; border: none; border-radius: 12px; font-weight: 700; color: #64748b; cursor: pointer;">
                        <i class="fas fa-times"></i> Simular RECHAZADO
                    </button>
                </form>
            </div>

            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #f1f5f9;">
            <a href="{{ route('empresa.proyectos.detalle', $proyecto->id) }}" style="font-size: 14px; color: var(--primary); font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Volver al proyecto
            </a>
            </div>
        </div>
    </div>
</div>
@endsection
