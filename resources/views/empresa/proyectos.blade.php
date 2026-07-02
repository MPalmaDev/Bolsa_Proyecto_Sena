@extends('layouts.dashboard')
@section('title', 'Mis Proyectos')
@section('page-title', 'Mis Proyectos')

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
    @vite(['resources/css/empresa.css'])

@endsection

@php $breadcrumbs = [['label' => 'Inicio', 'url' => route('empresa.dashboard')], ['label' => 'Proyectos']]; @endphp

@section('content')
<div class="animate-fade-in" style="padding-bottom: 40px;">

    {{-- Hero --}}
    <div class="instructor-hero">
        <div class="instructor-hero-bg-icon"><i class="fas fa-project-diagram"></i></div>
        <div style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <span class="instructor-tag">Convocatorias</span>
            </div>
            <h1 class="instructor-title">Portafolio de <span style="color: var(--primary);">Proyectos</span></h1>
            <p style="color: rgba(255,255,255,0.6); font-size: 16px; font-weight: 500;">Gestiona el ciclo completo de tus proyectos: postulantes, participantes y etapas.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card" style="background: linear-gradient(135deg, #0a1a15, #1a2e28); border: none;">
            <div class="stat-icon" style="background: rgba(255,255,255,0.1); color: white;">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="stat-num" style="color: white;">{{ $proyectos->total() }}</div>
                <div class="stat-label" style="color: rgba(255,255,255,0.6);">Total</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="stat-num" style="color: #10b981;">{{ $proyectos->where('estado', 'aprobado')->count() }}</div>
                <div class="stat-label" style="color: var(--text-lighter);">Activos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="stat-num" style="color: #f59e0b;">{{ $proyectos->where('estado', 'pendiente')->count() }}</div>
                <div class="stat-label" style="color: var(--text-lighter);">En revisión</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fee2e2; color: #ef4444;">
                <i class="fas fa-times-circle"></i>
            </div>
            <div>
                <div class="stat-num" style="color: #ef4444;">{{ $proyectos->where('estado', 'rechazado')->count() }}</div>
                <div class="stat-label" style="color: var(--text-lighter);">Rechazados</div>
            </div>
        </div>
    </div>

    {{-- Projects --}}
    @if($proyectos->isNotEmpty())
        <div id="empresa-projects-list">
        @foreach($proyectos as $proyecto)
            @php
                $badge = match($proyecto->estado) {
                    'completado'   => ['bg' => '#065f46', 'icon' => 'fa-check'],
                    'aprobado'     => ['bg' => '#10b981', 'icon' => 'fa-check'],
                    'pendiente'    => ['bg' => '#f59e0b', 'icon' => 'fa-clock'],
                    'rechazado'    => ['bg' => '#ef4444', 'icon' => 'fa-times'],
                    'cerrado'      => ['bg' => '#64748b', 'icon' => 'fa-lock'],
                    'en_progreso'  => ['bg' => '#3b82f6', 'icon' => 'fa-spinner'],
                    default        => ['bg' => '#64748b', 'icon' => 'fa-info-circle'],
                };
                $isActive = in_array($proyecto->estado, ['aprobado', 'en_progreso', 'completado']);
            @endphp

            <div class="project-card" data-proyecto-id="{{ $proyecto->id }}" data-proyecto-estado="{{ $proyecto->estado }}">
                {{-- Header --}}
                <div class="project-header">
                    <div class="project-title-group">
                        <div class="project-thumb">
                            @if($proyecto->imagen_url)
                                <img src="{{ $proyecto->imagen_url }}" loading="lazy">
                            @else
                                <i class="fas fa-rocket"></i>
                            @endif
                        </div>
                        <div>
                            <h3>{{ $proyecto->titulo }}</h3>
                            <div class="project-id">ID: PROJ-{{ str_pad($proyecto->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        @if($proyecto->tipo_publicacion_badge)
                            @php $vb = $proyecto->tipo_publicacion_badge; @endphp
                            <span style="background: {{ $vb['bg'] }}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas {{ $vb['icon'] }}"></i> {{ $vb['label'] }}
                            </span>
                        @endif
                        <span class="status-badge" style="background: {{ $badge['bg'] }}; color: white;">
                            <i class="fas {{ $badge['icon'] }}"></i>
                            {{ Str::title(str_replace('_', ' ', $proyecto->estado)) }}
                        </span>
                    </div>
                </div>

                {{-- Meta --}}
                <div class="project-meta">
                    <span class="meta-item">
                        <i class="fas fa-tag"></i> {{ $proyecto->categoria }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-hourglass"></i> {{ $proyecto->duracion_estimada_dias }} días
                    </span>
                    <span class="meta-item">
                        <i class="far fa-calendar-alt"></i>
                        {{ \Carbon\Carbon::parse($proyecto->fecha_publicacion)->translatedFormat('d M, Y') }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-users"></i> {{ $proyecto->postulaciones_count }} postulantes
                    </span>
                    @if($proyecto->instructor)
                    <span class="meta-item">
                        <i class="fas fa-chalkboard-teacher"></i> {{ $proyecto->instructor->nombres }}
                    </span>
                    @endif
                    <span class="meta-item">
                        <i class="fas fa-layer-group"></i> {{ $proyecto->etapas_count }} etapas
                    </span>
                </div>

                {{-- Cycle Flow --}}
                <div class="cycle-flow">
                    <a href="{{ route('empresa.proyectos.detalle', $proyecto->id) }}" class="cycle-step cycle-active">
                        <span class="step-dot"><i class="fas fa-info"></i></span>
                        Detalles
                        <span class="step-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>

                    @if($isActive)
                        <a href="{{ route('empresa.proyectos.postulantes', $proyecto->id) }}" class="cycle-step cycle-done">
                            <span class="step-dot"><i class="fas fa-users"></i></span>
                            Postulantes
                            <span class="step-arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="{{ route('empresa.proyectos.participantes', $proyecto->id) }}" class="cycle-step">
                            <span class="step-dot"><i class="fas fa-user-graduate"></i></span>
                            Participantes
                            <span class="step-arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="{{ route('empresa.proyectos.reporte', $proyecto->id) }}" class="cycle-step">
                            <span class="step-dot"><i class="fas fa-chart-simple"></i></span>
                            Etapas
                        </a>
                    @else
                        <span class="cycle-step">
                            <span class="step-dot"><i class="fas fa-users"></i></span>
                            Postulantes
                            <span class="step-arrow"><i class="fas fa-chevron-right"></i></span>
                        </span>
                        <span class="cycle-step">
                            <span class="step-dot"><i class="fas fa-user-graduate"></i></span>
                            Participantes
                            <span class="step-arrow"><i class="fas fa-chevron-right"></i></span>
                        </span>
                        <span class="cycle-step">
                            <span class="step-dot"><i class="fas fa-chart-simple"></i></span>
                            Etapas
                        </span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="project-actions">
                    <a href="{{ route('empresa.proyectos.edit', $proyecto->id) }}" class="btn-action btn-action-primary">
                        <i class="fas fa-pen-to-square"></i> Editar
                    </a>
                    @if($proyecto->estado != 'cerrado')
                    <form action="{{ route('empresa.proyectos.destroy', $proyecto->id) }}" method="POST" id="cerrar-form-{{ $proyecto->id }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                    </form>
                    <button type="button" onclick="openConfirm('¿Cerrar proyecto?', 'El proyecto &quot;{{ Str::limit($proyecto->titulo, 30) }}&quot; ya no será visible para nuevos aprendices.', () => document.getElementById('cerrar-form-{{ $proyecto->id }}').submit())" class="btn-action btn-action-red">
                        <i class="fas fa-lock"></i> Cerrar
                    </button>
                    @endif
                </div>
            </div>
        @endforeach

        @if($proyectos->hasPages())
            <div id="empresa-projects-pagination" style="margin-top: 32px; display: flex; justify-content: center;">
                {{ $proyectos->withQueryString()->links() }}
            </div>
        @endif
        </div>{{-- /empresa-projects-list --}}
    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
            <h3>Aún no tienes convocatorias</h3>
            <p>Inicia hoy mismo publicando tu primer proyecto para atraer el talento que necesitas.</p>
            <a href="{{ route('empresa.proyectos.crear') }}" class="btn-premium" style="padding: 14px 28px;">
                <i class="fas fa-rocket"></i> Empezar Ahora
            </a>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
(function () {
    if (!document.getElementById('empresa-projects-list')) return;

    let rtDebounce = null;

    function refreshEmpresaProjects() {
        fetch(window.location.href)
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newList = doc.getElementById('empresa-projects-list');
                const newPag  = doc.getElementById('empresa-projects-pagination');
                const curList = document.getElementById('empresa-projects-list');
                const curPag  = document.getElementById('empresa-projects-pagination');
                if (newList && curList) {
                    curList.innerHTML = newList.innerHTML;
                }
                if (newPag && curPag) curPag.innerHTML = newPag.innerHTML;
                if (!newPag && curPag) curPag.innerHTML = '';
            })
            .catch(() => {});
    }

    window.addEventListener('realtime:proyecto', function (e) {
        clearTimeout(rtDebounce);
        rtDebounce = setTimeout(refreshEmpresaProjects, 800);
    });
})();
</script>
@endsection


