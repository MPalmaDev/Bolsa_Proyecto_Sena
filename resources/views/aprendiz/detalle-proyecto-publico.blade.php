@extends('layouts.dashboard')

@section('title', 'Detalle del Proyecto')
@section('page-title', 'Detalle del Proyecto')

@section('sidebar-nav')
    <span class="nav-label">Principal</span>
    <a href="{{ route('aprendiz.dashboard') }}" class="nav-item {{ request()->routeIs('aprendiz.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> <span>Principal</span>
    </a>
    <a href="{{ route('aprendiz.proyectos') }}" class="nav-item {{ request()->routeIs('aprendiz.proyectos') ? 'active' : '' }}">
        <i class="fas fa-briefcase"></i> <span>Explorar Proyectos</span>
    </a>
    <a href="{{ route('aprendiz.postulaciones') }}" class="nav-item {{ request()->routeIs('aprendiz.postulaciones') ? 'active' : '' }}">
        <i class="fas fa-paper-plane"></i> <span>Mis Postulaciones</span>
    </a>
    <a href="{{ route('aprendiz.historial') }}" class="nav-item {{ request()->routeIs('aprendiz.historial') ? 'active' : '' }}">
        <i class="fas fa-history"></i> <span>Historial</span>
    </a>
    <a href="{{ route('aprendiz.entregas') }}" class="nav-item {{ request()->routeIs('aprendiz.entregas') ? 'active' : '' }}">
        <i class="fas fa-tasks"></i> <span>Mis Entregas</span>
    </a>
    <span class="nav-label">Cuenta</span>
    <a href="{{ route('aprendiz.perfil') }}" class="nav-item {{ request()->routeIs('aprendiz.perfil') ? 'active' : '' }}">
        <i class="fas fa-user"></i> <span>Mi Perfil</span>
    </a>
@endsection

@section('styles')
    @vite(['resources/css/aprendiz.css'])
@endsection

@php $breadcrumbs = [['label' => 'Inicio', 'url' => route('aprendiz.dashboard')], ['label' => 'Detalle del Proyecto']]; @endphp
@section('content')
<div class="animate-fade-in" style="padding-bottom: 40px;">

    <!-- Hero Header -->
    <div class="instructor-hero" style="padding: 40px 48px; margin-bottom: 32px;">
        <div class="instructor-hero-bg-icon"><i class="fas fa-project-diagram"></i></div>
        <div style="position: relative; z-index: 1;">
            <a href="{{ url()->previous() }}" style="display: inline-flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 16px; transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; flex-wrap: wrap;">
                <span class="instructor-tag">Oportunidad</span>
                <span style="background: rgba(59,130,246,0.2); border: 1px solid rgba(59,130,246,0.3); color: #93c5fd; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">{{ $proyecto->categoria }}</span>
                @if($proyecto->tipo_publicacion_badge)
                    @php $b = $proyecto->tipo_publicacion_badge; @endphp
                    <span style="background: {{ $b['bg'] }}; color: white; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                        <i class="fas {{ $b['icon'] }}"></i> {{ $b['label'] }}
                    </span>
                @endif
            </div>
            <h1 class="instructor-title">{{ $proyecto->titulo }}</h1>
            <p style="color: rgba(255,255,255,0.7); font-size: 15px; font-weight: 500;">Conoce todos los detalles y decide si este proyecto es para ti.</p>
        </div>
    </div>

    <!-- Bento Grid Stats -->
    <div class="instructor-stat-grid" style="margin-bottom: 32px;">
        <div class="glass-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-top: 4px solid #3b82f6;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fas fa-building"></i>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Empresa</div>
                <div style="font-size: 18px; font-weight: 800; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $proyecto->emp_nombre }}</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-top: 4px solid #3eb489;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(62,180,137,0.1); color: #3eb489; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fas fa-clock"></i>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Duración</div>
                <div style="font-size: 18px; font-weight: 800; color: var(--text);">{{ $proyecto->duracion_estimada_dias }} días</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-top: 4px solid #f59e0b;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: #fffbeb; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Instructor</div>
                <div style="font-size: 18px; font-weight: 800; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $proyecto->instructor_nombre }}</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-top: 4px solid #ef4444;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div style="min-width: 0;">
                <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Fecha Límite</div>
                <div style="font-size: 18px; font-weight: 800; color: var(--text);">{{ \Carbon\Carbon::parse($proyecto->fecha_finalizacion)->format('d M, Y') }}</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 28px; align-items: start;">
        <!-- Main Info -->
        <div style="display: grid; gap: 28px;">

            <!-- Descripción -->
            <div class="glass-card" style="padding: 28px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <i class="fas fa-info-circle" style="color: #3eb489; font-size: 18px;"></i>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--text); margin: 0;">Sobre el Proyecto</h3>
                </div>
                <p style="color: var(--text-light); line-height: 1.8; margin-bottom: 24px; font-weight: 500;">
                    {{ $proyecto->descripcion }}
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #eef2f6;">
                        <h4 style="font-size: 12px; font-weight: 800; color: var(--text); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-list-check" style="color: #3eb489; margin-right: 6px;"></i>Requisitos
                        </h4>
                        <p style="font-size: 13px; color: var(--text-light); font-weight: 500; line-height: 1.6; margin: 0;">{{ $proyecto->requisitos_especificos ?? 'No especificados' }}</p>
                    </div>
                    <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #eef2f6;">
                        <h4 style="font-size: 12px; font-weight: 800; color: var(--text); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-star" style="color: #f59e0b; margin-right: 6px;"></i>Habilidades
                        </h4>
                        <p style="font-size: 13px; color: var(--text-light); font-weight: 500; line-height: 1.6; margin: 0;">{{ $proyecto->habilidades_requeridas ?? 'No especificadas' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="display: grid; gap: 24px;">

            <!-- Imagen del Proyecto + mini stats -->
            <div class="glass-card" style="padding: 24px;">
                @if($proyecto->imagen_url)
                    <img src="{{ $proyecto->imagen_url }}" loading="lazy" style="width: 100%; border-radius: 14px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); margin-bottom: 16px;">
                @else
                    <div style="width: 100%; aspect-ratio: 16/9; background: linear-gradient(135deg, #3eb489, #2d9d74); border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; margin-bottom: 16px;">
                        <i class="fas fa-image" style="font-size: 36px; margin-bottom: 10px; opacity: 0.7;"></i>
                        <p style="font-size: 13px; font-weight: 700;">Proyecto Sin Imagen</p>
                    </div>
                @endif
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="background: #f8fafc; padding: 12px; border-radius: 10px; text-align: center; border: 1px solid #eef2f6;">
                        <p style="font-size: 10px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Categoría</p>
                        <p style="font-size: 12px; font-weight: 800; color: var(--text); margin: 0;">{{ $proyecto->categoria }}</p>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 10px; text-align: center; border: 1px solid #eef2f6;">
                        <p style="font-size: 10px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px;">Duración</p>
                        <p style="font-size: 12px; font-weight: 800; color: var(--text); margin: 0;">{{ $proyecto->duracion_estimada_dias ?? '—' }} días</p>
                    </div>
                </div>
            </div>

            <!-- Oferta / Beneficio -->
            @if($proyecto->oferta)
            <div class="glass-card" style="padding: 0; overflow: hidden; border: 1.5px solid rgba(139,92,246,0.2); box-shadow: 0 4px 16px rgba(139,92,246,0.1);">
                <div style="height: 4px; background: linear-gradient(90deg, #8b5cf6, #6d28d9, #8b5cf6);"></div>
                <div style="padding: 18px 20px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 4px 12px rgba(139,92,246,0.3); flex-shrink: 0;">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div>
                            <p style="font-size: 10px; font-weight: 800; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Beneficio</p>
                            <p style="font-size: 14px; font-weight: 800; color: #4c1d95; margin: 2px 0 0 0;">
                                @switch($proyecto->oferta)
                                    @case('pasantias') Pasantías @break
                                    @case('contrato_aprendizaje') Contrato de aprendizaje @break
                                    @case('auxilio_transporte') Auxilio de transporte @break
                                    @case('otro') {{ $proyecto->oferta_otro }} @break
                                @endswitch
                            </p>
                        </div>
                    </div>
                    <div style="background: rgba(139,92,246,0.06); border-radius: 8px; padding: 8px 12px; border: 1px solid rgba(139,92,246,0.1);">
                        <p style="font-size: 11px; color: #7c3aed; margin: 0; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-star" style="font-size: 9px;"></i> Solo al aprendiz con mejor desempeño.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Cronograma compacto -->
            <div class="glass-card" style="padding: 20px;">
                <h4 style="font-size: 13px; font-weight: 800; color: var(--text); margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-calendar-alt" style="color: #3eb489;"></i>Cronograma
                </h4>
                <div style="display: grid; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: #f8fafc; border-radius: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-rocket" style="font-size: 14px;"></i>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: var(--text-light); margin-bottom: 1px; text-transform: uppercase; font-weight: 700;">Lanzamiento</p>
                            <p style="font-size: 13px; font-weight: 800; color: var(--text); margin: 0;">{{ \Carbon\Carbon::parse($proyecto->fecha_publicacion)->format('d M, Y') }}</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: #fef2f2; border-radius: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-flag-checkered" style="font-size: 14px;"></i>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: var(--text-light); margin-bottom: 1px; text-transform: uppercase; font-weight: 700;">Fecha Límite</p>
                            <p style="font-size: 13px; font-weight: 800; color: #ef4444; margin: 0;">{{ \Carbon\Carbon::parse($proyecto->fecha_finalizacion)->format('d M, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ubicación -->
            @if($proyecto->latitud && $proyecto->longitud)
            <div class="glass-card" style="padding: 20px;">
                <h4 style="font-size: 13px; font-weight: 800; color: var(--text); margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i>Ubicación
                </h4>
                <div style="border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                    <div id="ubicacion-map" style="width: 100%; height: 180px;"></div>
                </div>
            </div>
            @endif

            <!-- Acción: Postularse -->
            <div class="glass-card" style="padding: 24px; position: sticky; top: 100px;">
                @if($postulacion)
                    @php
                        $statusColors = match($postulacion->estado) {
                            'aceptada' => ['bg' => '#10b981', 'icon' => 'fa-check-circle', 'text' => 'Aceptada'],
                            'pendiente' => ['bg' => '#f59e0b', 'icon' => 'fa-clock', 'text' => 'Pendiente'],
                            'en_revision' => ['bg' => '#3b82f6', 'icon' => 'fa-spinner', 'text' => 'En Revisión'],
                            'rechazada' => ['bg' => '#ef4444', 'icon' => 'fa-times-circle', 'text' => 'Rechazada'],
                            default => ['bg' => '#64748b', 'icon' => 'fa-info-circle', 'text' => ucfirst($postulacion->estado)],
                        };
                    @endphp
                    <div style="text-align: center;">
                        <div style="width: 56px; height: 56px; background: {{ $statusColors['bg'] }}; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 14px; box-shadow: 0 6px 16px rgba(0,0,0,0.08);">
                            <i class="fas {{ $statusColors['icon'] }}"></i>
                        </div>
                        <h4 style="font-size: 17px; font-weight: 800; color: var(--text); margin-bottom: 6px;">Ya te postulaste</h4>
                        <p style="font-size: 12px; color: var(--text-light); font-weight: 500; margin-bottom: 4px;">Estado de tu postulación:</p>
                        <span style="display: inline-block; background: {{ $statusColors['bg'] }}; color: white; padding: 6px 18px; border-radius: 20px; font-size: 13px; font-weight: 700;">
                            <i class="fas {{ $statusColors['icon'] }}" style="margin-right: 6px;"></i>{{ $statusColors['text'] }}
                        </span>
                        @if($postulacion->estado === 'aceptada')
                            <div style="margin-top: 18px;">
                                <a href="{{ route('aprendiz.proyecto.detalle', $proyecto->id) }}" class="btn-premium" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px;">
                                    <i class="fas fa-tasks"></i> Ir a la gestión
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div style="text-align: center;">
                        <div style="width: 56px; height: 56px; background: rgba(62,180,137,0.1); color: #3eb489; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 14px;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h4 style="font-size: 17px; font-weight: 800; color: var(--text); margin-bottom: 6px;">¿Te interesa este proyecto?</h4>
                        <p style="font-size: 12px; color: var(--text-light); font-weight: 500; margin-bottom: 18px;">Postúlate y comienza a construir tu futuro.</p>
                        @if($puedePostular)
                            <form action="{{ route('aprendiz.postular', $proyecto->id) }}" method="POST">
                                @csrf
                                <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #3eb489, #2d9d74); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 6px 18px rgba(62,180,137,0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 22px rgba(62,180,137,0.4)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 18px rgba(62,180,137,0.3)'">
                                    <i class="fas fa-paper-plane"></i> Postularme
                                </button>
                            </form>
                        @else
                            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px; color: #dc2626; font-size: 13px; font-weight: 600; text-align: center;">
                                <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i> {{ $mensaje ?? 'No es posible postularte en este momento.' }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($proyecto->latitud && $proyecto->longitud)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@vite(['resources/js/maps.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    initViewMap('ubicacion-map', {{ $proyecto->latitud }}, {{ $proyecto->longitud }}, '{{ $proyecto->nombre }}');
});
</script>
@endif
@endsection
