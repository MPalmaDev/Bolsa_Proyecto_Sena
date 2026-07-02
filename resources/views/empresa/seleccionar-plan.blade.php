@extends('layouts.dashboard')
@section('title', 'Plan de Visibilidad')
@section('page-title', 'Plan de Visibilidad')

@section('sidebar-nav')
    <span class="nav-label">Portal Empresa</span>
    <a href="{{ route('empresa.dashboard') }}" class="nav-item {{ request()->routeIs('empresa.dashboard') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> Principal
    </a>
    <a href="{{ route('empresa.proyectos') }}" class="nav-item {{ request()->routeIs('empresa.proyectos') ? 'active' : '' }}">
        <i class="fas fa-project-diagram"></i> Mis Proyectos
    </a>
    <a href="{{ route('empresa.proyectos.crear') }}" class="nav-item {{ request()->routeIs('empresa.proyectos.crear') ? 'active' : '' }}">
        <i class="fas fa-plus-circle"></i> Publicar Proyecto
    </a>
    <span class="nav-label">Configuración</span>
    <a href="{{ route('empresa.perfil') }}" class="nav-item {{ request()->routeIs('empresa.perfil') ? 'active' : '' }}">
        <i class="fas fa-building"></i> Perfil Empresa
    </a>
@endsection

@section('styles')
    <style>
        .plan-card {
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }
        .plan-card.selected {
            border-color: #3eb489;
            box-shadow: 0 20px 40px rgba(62,180,137,0.15);
        }
        .plan-card.gratuito:hover {
            border-color: #94a3b8;
        }
        .plan-card .plan-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: white;
        }
        .plan-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 20px;
        }
        .plan-precio {
            font-size: 42px;
            font-weight: 900;
            color: var(--text);
        }
        .plan-precio small {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-light);
        }
        .plan-desc {
            font-size: 13px;
            color: var(--text-light);
            font-weight: 500;
            margin: 12px 0 24px;
            line-height: 1.6;
        }
        .plan-features {
            text-align: left;
            margin: 20px 0;
            padding: 0;
            list-style: none;
        }
        .plan-features li {
            padding: 8px 0;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .plan-features li i {
            width: 20px;
            text-align: center;
        }
        .plan-features li .fa-check { color: #10b981; }
        .plan-features li .fa-times { color: #ef4444; }
    </style>
@endsection

@php $breadcrumbs = [['label' => 'Inicio', 'url' => route('empresa.dashboard')], ['label' => 'Proyectos', 'url' => route('empresa.proyectos')], ['label' => 'Plan de Visibilidad']]; @endphp
@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding-bottom: 40px;">
    <div style="background: linear-gradient(135deg, #0a1a15, #1a2e28); border-radius: 32px; padding: 48px; margin-bottom: 40px; text-align: center;">
        <h1 style="color: white; font-size: 32px; font-weight: 900; margin-bottom: 12px;">Impulsa tu <span style="color: #3eb489;">Proyecto</span></h1>
        <p style="color: rgba(255,255,255,0.6); font-size: 16px; max-width: 600px; margin: 0 auto;">Selecciona el nivel de visibilidad que mejor se adapte a tus necesidades. Sin suscripciones, solo pagos únicos.</p>
    </div>

    <form method="GET" action="{{ route('empresa.proyectos.pago', $proyecto->id) }}" id="plan-form">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
            {{-- Gratuito --}}
            <div class="plan-card gratuito" data-tipo="gratuito" onclick="selectPlan('gratuito')">
                <div class="plan-icon" style="background: #f1f5f9; color: #64748b;">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 8px;">Gratuito</h3>
                <div class="plan-precio" style="color: #64748b;">$0 <small>COP</small></div>
                <p class="plan-desc">Publicación básica sin costo</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Publicación en su categoría</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Orden cronológico</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Funciones básicas completas</li>
                    <li><i class="fas fa-times" style="color:#ef4444;"></i> Posición destacada</li>
                    <li><i class="fas fa-times" style="color:#ef4444;"></i> Página principal</li>
                </ul>
                <p style="font-size: 12px; color: var(--text-light); font-weight: 600;">Siempre gratis</p>
            </div>

            {{-- Destacado --}}
            <div class="plan-card" data-tipo="destacado" onclick="selectPlan('destacado')">
                <div class="plan-badge" style="background: linear-gradient(135deg,#3b82f6,#2563eb);">
                    <i class="fas fa-star"></i> DESTACADO
                </div>
                <div class="plan-icon" style="background: #eff6ff; color: #3b82f6;">
                    <i class="fas fa-star"></i>
                </div>
                <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 8px;">Destacado</h3>
                <div class="plan-precio">${{ number_format($precios['destacado']['precio']) }} <small>COP</small></div>
                <p class="plan-desc">{{ $precios['destacado']['descripcion'] }}</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Todo lo del plan Gratuito</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Primeros lugares en su categoría</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Insignia "Destacado" visible</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> {{ $precios['destacado']['dias'] }} días de duración</li>
                    <li><i class="fas fa-times" style="color:#ef4444;"></i> Página principal</li>
                </ul>
                <p style="font-size: 12px; color: var(--text-light); font-weight: 600;">Pago único · {{ $precios['destacado']['dias'] }} días</p>
            </div>

            {{-- Patrocinado --}}
            <div class="plan-card" data-tipo="patrocinado" onclick="selectPlan('patrocinado')">
                <div class="plan-badge" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
                    <i class="fas fa-crown"></i> PATROCINADO
                </div>
                <div class="plan-icon" style="background: #fffbeb; color: #f59e0b;">
                    <i class="fas fa-crown"></i>
                </div>
                <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 8px;">Patrocinado</h3>
                <div class="plan-precio">${{ number_format($precios['patrocinado']['precio']) }} <small>COP</small></div>
                <p class="plan-desc">{{ $precios['patrocinado']['descripcion'] }}</p>
                <ul class="plan-features">
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Todo lo del plan Destacado</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Página principal de la plataforma</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Notificación a aprendices afines</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Insignia "Patrocinado" visible</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> {{ $precios['patrocinado']['dias'] }} días de duración</li>
                </ul>
                <p style="font-size: 12px; color: var(--text-light); font-weight: 600;">Pago único · {{ $precios['patrocinado']['dias'] }} días</p>
            </div>
        </div>

        <input type="hidden" name="tipo" id="selected-tipo" value="">

        <div style="display: flex; gap: 20px; justify-content: center; margin-top: 40px;">
            <a href="{{ route('empresa.proyectos.detalle', $proyecto->id) }}" class="btn-premium" style="background: white; color: var(--text-light); border: 1px solid #e2e8f0; box-shadow: none; padding: 14px 28px;">
                Cancelar
            </a>
            <button type="submit" id="btn-continuar" class="btn-premium" style="padding: 14px 40px; opacity: 0.5; pointer-events: none;" disabled>
                Continuar al Pago <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function selectPlan(tipo) {
    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
    document.querySelector('.plan-card[data-tipo="' + tipo + '"]').classList.add('selected');
    document.getElementById('selected-tipo').value = tipo;
    const btn = document.getElementById('btn-continuar');
    if (tipo === 'gratuito') {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';
        document.getElementById('plan-form').action = '{{ route("empresa.proyectos.detalle", $proyecto->id) }}';
        window.location.href = btn.form.action;
    } else {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto';
    }
}
</script>
@endsection
