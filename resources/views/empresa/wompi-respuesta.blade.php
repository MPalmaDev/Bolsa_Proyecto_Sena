@extends('layouts.dashboard')
@section('title', 'Resultado del Pago')
@section('page-title', 'Resultado del Pago')

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
        <div style="background: linear-gradient(135deg, #0a1a15, #1a2e28); padding: 40px 48px; text-align: center;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: {{ $exito ? 'rgba(34,197,94,0.2)' : ($exito === false ? 'rgba(239,68,68,0.2)' : 'rgba(251,191,36,0.2)') }}; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                @if($exito === true)
                    <i class="fas fa-check-circle" style="font-size: 36px; color: #22c55e;"></i>
                @elseif($exito === false)
                    <i class="fas fa-times-circle" style="font-size: 36px; color: #ef4444;"></i>
                @else
                    <i class="fas fa-clock" style="font-size: 36px; color: #f59e0b;"></i>
                @endif
            </div>
            <h2 style="color: white; font-size: 24px; font-weight: 900;">
                @if($exito === true)
                    Pago Aprobado
                @elseif($exito === false)
                    Pago No Aprobado
                @else
                    Pago en Proceso
                @endif
            </h2>
            <p style="color: rgba(255,255,255,0.6); font-size: 14px;">Wompi — Pasarela de Pagos</p>
        </div>

        <div style="padding: 40px 48px; text-align: center;">
            <p style="font-size: 15px; color: var(--text-light); font-weight: 600; margin-bottom: 24px;">
                {{ $mensaje }}
            </p>

            <div style="background: #f8fafc; border-radius: 16px; padding: 24px; margin-bottom: 24px; text-align: left;">
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px;">
                    <span style="color: var(--text-light);">Proyecto</span>
                    <span style="font-weight: 700;">{{ $proyecto->titulo }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px;">
                    <span style="color: var(--text-light);">Plan</span>
                    <span style="font-weight: 700;">{{ ucfirst($pago->tipo) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px;">
                    <span style="color: var(--text-light);">Monto</span>
                    <span style="font-weight: 700;">${{ number_format($pago->monto) }} COP</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px;">
                    <span style="color: var(--text-light);">Estado</span>
                    <span style="font-weight: 700; color: {{ $exito === true ? '#22c55e' : ($exito === false ? '#ef4444' : '#f59e0b') }};">
                        {{ ucfirst($pago->estado) }}
                    </span>
                </div>
            </div>

            <a href="{{ route('empresa.proyectos.detalle', $proyecto->id) }}" class="btn-premium" style="display: inline-block; padding: 14px 32px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Volver al Proyecto
            </a>
        </div>
    </div>
</div>
@endsection
