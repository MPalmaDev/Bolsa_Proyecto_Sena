@extends('layouts.dashboard')

@section('title', 'Detalle del Proyecto | ' . $proyecto->titulo)
@section('page-title', 'Detalle de Proyecto')

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

@php $breadcrumbs = [['label' => 'Inicio', 'url' => route('empresa.dashboard')], ['label' => 'Proyectos', 'url' => route('empresa.proyectos')], ['label' => 'Detalle']]; @endphp
@section('content')
<div class="animate-fade-in" style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px; padding-bottom: 40px; align-items: start;">
    
    <!-- Main Content -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Hero Image -->
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="width: 100%; height: 240px; position: relative;">
                <img src="{{ $proyecto->imagen_url ?: asset('assets/proyecto_default.jpg') }}" loading="lazy" alt="Proyecto" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; inset: 0; background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 60%);"></div>
                <div style="position: absolute; bottom: 20px; left: 24px;">
                    <span style="background: #3eb489; color: white; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: inline-block;">{{ $proyecto->categoria }}</span>
                    <h2 style="color: white; font-size: 1.6rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">{{ $proyecto->titulo }}</h2>
                </div>
            </div>
            
            <div style="padding: 28px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 16px;">Descripción de la Convocatoria</h3>
                <p style="color: var(--text-light); line-height: 1.8; font-size: 14px; font-weight: 500;">
                    {{ $proyecto->descripcion }}
                </p>
            </div>
        </div>

        <!-- Requirements Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="glass-card" style="padding: 24px;">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59,130,246,0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h4 style="font-weight: 800; font-size: 15px; color: var(--text);">Requisitos Técnicos</h4>
                </div>
                <p style="color: var(--text-light); font-size: 13px; line-height: 1.6; font-weight: 500;">{{ $proyecto->requisitos_especificos }}</p>
            </div>

            <div class="glass-card" style="padding: 24px;">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(62,180,137,0.1); color: #3eb489; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4 style="font-weight: 800; font-size: 15px; color: var(--text);">Habilidades Buscadas</h4>
                </div>
                <p style="color: var(--text-light); font-size: 13px; line-height: 1.6; font-weight: 500;">{{ $proyecto->habilidades_requeridas }}</p>
            </div>
        </div>

        <!-- Stages Section -->
        <div class="glass-card" style="padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--text);">
                    <i class="fas fa-tasks" style="color: #3eb489; margin-right: 10px;"></i>Estructura del Proyecto
                </h3>
                <span style="font-size: 12px; color: var(--text-light); font-weight: 700;">{{ count($etapas) }} Etapas Definidas</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                @forelse($etapas as $index => $etapa)
                    <div style="display: flex; gap: 20px;">
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $index == 0 ? 'linear-gradient(135deg, #3eb489, #2d9d74)' : '#f8fafc' }}; color: {{ $index == 0 ? 'white' : 'var(--text-light)' }}; border: 2px solid {{ $index == 0 ? '#3eb489' : '#e2e8f0' }}; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; flex-shrink: 0; box-shadow: {{ $index == 0 ? '0 4px 12px rgba(62,180,137,0.3)' : 'none' }};">
                                {{ $index + 1 }}
                            </div>
                            @if(!$loop->last)
                                <div style="width: 2px; flex: 1; min-height: 40px; background: #e2e8f0; margin: 8px 0;"></div>
                            @endif
                        </div>
                        <div style="flex: 1; padding-bottom: 20px;">
                            <h5 style="font-weight: 800; color: var(--text); margin-bottom: 4px; font-size: 15px;">{{ $etapa->nombre }}</h5>
                            <p style="font-size: 13px; color: var(--text-light); line-height: 1.6;">{{ $etapa->descripcion }}</p>

                            @if(isset($evidenciasAprobadas[$etapa->id]) && count($evidenciasAprobadas[$etapa->id]) > 0)
                                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                                    <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #3eb489; font-weight: 800; margin-bottom: 12px;">
                                        <i class="fas fa-check-circle"></i> Evidencias Aprobadas
                                    </p>
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        @foreach($evidenciasAprobadas[$etapa->id] as $evidencia)
                                            <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;">
                                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fas fa-user-graduate" style="font-size: 12px;"></i>
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <p style="font-size: 13px; font-weight: 700; color: var(--text);">
                                                        {{ $evidencia->aprendiz->nombres ?? '' }} {{ $evidencia->aprendiz->apellidos ?? '' }}
                                                    </p>
                                                    <p style="font-size: 11px; color: #16a34a; font-weight: 600;">
                                                        {{ $evidencia->fecha_envio->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                                @if($evidencia->ruta_archivo)
                                                    <a href="{{ asset('storage/' . $evidencia->ruta_archivo) }}" target="_blank" style="width: 34px; height: 34px; border-radius: 8px; background: white; border: 1px solid #bbf7d0; color: #16a34a; display: flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0;" title="Descargar archivo">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                                @if($evidencia->comentario_instructor)
                                                    <div style="position: relative;" title="{{ $evidencia->comentario_instructor }}">
                                                        <span style="width: 34px; height: 34px; border-radius: 8px; background: white; border: 1px solid #bbf7d0; color: #6b7280; display: flex; align-items: center; justify-content: center; cursor: help; flex-shrink: 0;">
                                                            <i class="fas fa-comment"></i>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 16px; border: 2px dashed #e2e8f0;">
                        <i class="fas fa-stream" style="font-size: 32px; color: var(--text-lighter); margin-bottom: 12px;"></i>
                        <p style="color: var(--text-light); font-weight: 600;">El instructor aún no ha definido las etapas de este proyecto.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div style="display: flex; flex-direction: column; gap: 20px; position: sticky; top: 24px;">
        
        <!-- Status Card -->
        <div class="glass-card" style="padding: 24px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-light); font-weight: 800; margin-bottom: 10px;">Estado Actual</p>
                @php
                $statusClass = match($proyecto->estado) {
                    'completado' => ['bg' => '#065f46', 'border' => '#065f46', 'text' => '#ffffff', 'icon' => 'fa-check'],
                    'aprobado' => ['bg' => '#10b981', 'border' => '#bbf7d0', 'text' => '#ffffff', 'icon' => 'fa-check'],
                    'pendiente' => ['bg' => '#f59e0b', 'border' => '#fde68a', 'text' => '#ffffff', 'icon' => 'fa-clock'],
                    'rechazado' => ['bg' => '#ef4444', 'border' => '#fecaca', 'text' => '#ffffff', 'icon' => 'fa-times'],
                    'cerrado' => ['bg' => '#64748b', 'border' => '#e2e8f0', 'text' => '#ffffff', 'icon' => 'fa-lock'],
                    'en_progreso' => ['bg' => '#3b82f6', 'border' => '#bfdbfe', 'text' => '#ffffff', 'icon' => 'fa-spinner'],
                    default => ['bg' => '#64748b', 'border' => '#e2e8f0', 'text' => '#ffffff', 'icon' => 'fa-info-circle'],
                };
                @endphp
                <span style="background: {{ $statusClass['bg'] }}; border: 1px solid {{ $statusClass['border'] }}; color: {{ $statusClass['text'] }}; font-size: 14px; padding: 8px 20px; border-radius: 20px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas {{ $statusClass['icon'] }}"></i> {{ Str::title(str_replace('_', ' ', $proyecto->estado)) }}
                </span>
            </div>
            
            <div style="display: grid; gap: 14px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: var(--text-light); font-weight: 600;">Duración:</span>
                    <span style="font-size: 14px; font-weight: 800; color: var(--text);">{{ $proyecto->duracion_estimada_dias }} días</span>
                </div>
                @if($proyecto->oferta)
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: var(--text-light); font-weight: 600;">Beneficio:</span>
                    <span style="font-size: 12px; font-weight: 800; background: linear-gradient(135deg, rgba(139,92,246,0.12), rgba(124,58,237,0.08)); color: #7c3aed; padding: 3px 12px 3px 8px; border-radius: 20px; border: 1px solid rgba(139,92,246,0.15); display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-gift" style="font-size: 9px;"></i>
                        @switch($proyecto->oferta)
                            @case('pasantias') Pasantías @break
                            @case('contrato_aprendizaje') Contrato aprendizaje @break
                            @case('auxilio_transporte') Auxilio transporte @break
                            @case('otro') {{ $proyecto->oferta_otro }} @break
                        @endswitch
                    </span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: var(--text-light); font-weight: 600;">Publicado:</span>
                    <span style="font-size: 14px; font-weight: 800; color: var(--text);">{{ \Carbon\Carbon::parse($proyecto->fecha_publicacion)->format('d/m/Y') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: var(--text-light); font-weight: 600;">Cierre:</span>
                    <span style="font-size: 14px; font-weight: 800; color: #ef4444;">{{ \Carbon\Carbon::parse($proyecto->fecha_finalizacion)->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Plan de Visibilidad -->
        @php $planBadge = $proyecto->tipo_publicacion_badge; @endphp
        <div class="glass-card" style="padding: 24px;">
            <h4 style="font-size: 14px; font-weight: 800; color: var(--text); margin-bottom: 16px;">
                <i class="fas fa-chart-line" style="color: #f59e0b; margin-right: 8px;"></i>Plan de Visibilidad
            </h4>
            @if($planBadge)
                <div style="background: {{ $planBadge['bg'] }}; border-radius: 12px; padding: 16px; text-align: center; margin-bottom: 12px;">
                    <i class="fas {{ $planBadge['icon'] }}" style="font-size: 28px; color: white; margin-bottom: 8px; display: block;"></i>
                    <span style="font-size: 18px; font-weight: 900; color: white;">{{ $planBadge['label'] }}</span>
                </div>
                <div style="display: grid; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-light); font-weight: 600;">Inicio:</span>
                        <span style="font-weight: 700;">{{ $proyecto->fecha_inicio_plan?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-light); font-weight: 600;">Vence:</span>
                        <span style="font-weight: 700; color: {{ $proyecto->fecha_fin_plan && $proyecto->fecha_fin_plan->isFuture() ? '#10b981' : '#ef4444' }};">
                            {{ $proyecto->fecha_fin_plan?->format('d/m/Y') ?? '-' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-light); font-weight: 600;">Días restantes:</span>
                        <span style="font-weight: 700;">
                            @if($proyecto->fecha_fin_plan)
                                {{ max(0, now()->diffInDays($proyecto->fecha_fin_plan, false)) }} días
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
                @if($proyecto->pago)
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9; font-size: 12px; color: var(--text-light);">
                        Pago: ${{ number_format($proyecto->pago->monto) }} COP · {{ ucfirst($proyecto->pago->metodo_pago) }}
                    </div>
                @endif
            @else
                <div style="text-align: center; padding: 8px 0;">
                    <p style="font-size: 13px; color: var(--text-light); font-weight: 600; margin-bottom: 16px;">Sin plan de visibilidad activo</p>
                    <a href="{{ route('empresa.proyectos.plan', $proyecto->id) }}" class="btn-premium" style="display: inline-flex; padding: 10px 20px; font-size: 13px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-rocket"></i> Impulsar Proyecto
                    </a>
                    <p style="font-size: 11px; color: var(--text-lighter); margin-top: 8px; font-weight: 500;">Destaca tu proyecto por ${{ number_format(config('app_config.publicacion.destacado.precio')) }} COP</p>
                </div>
            @endif
        </div>

        <!-- Instructor Assigned -->
        <div class="glass-card" style="padding: 24px;">
            <h4 style="font-size: 14px; font-weight: 800; color: var(--text); margin-bottom: 16px;">
                <i class="fas fa-chalkboard-teacher" style="color: #3eb489; margin-right: 8px;"></i>Instructor Responsable
            </h4>
            @if($proyecto->nombres)
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #3eb489, #2d9d74); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px;">
                        {{ substr($proyecto->nombres, 0, 1) }}
                    </div>
                    <div>
                        <p style="font-size: 14px; font-weight: 800; color: var(--text);">{{ $proyecto->nombres }} {{ $proyecto->apellidos }}</p>
                        <p style="font-size: 12px; color: #3eb489; font-weight: 600;">Instructor SENA</p>
                    </div>
                </div>
            @else
                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-light); padding: 16px; background: #f8fafc; border-radius: 12px;">
                    <i class="fas fa-user-clock" style="font-size: 20px;"></i>
                    <p style="font-size: 13px; font-weight: 600;">Pendiente por asignar.</p>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @if($proyecto->instructor)
            <form action="{{ route('chat.store') }}" method="POST">
                @csrf
                <input type="hidden" name="proyecto_id" value="{{ $proyecto->id }}">
                <input type="hidden" name="type" value="empresa_instructor">
                <button type="submit" class="btn-premium" style="width:100%;justify-content:center;background:#8b5cf6;padding:14px 20px;">
                    <i class="fas fa-comment-dots"></i> Chat con Instructor
                </button>
            </form>
            @endif
            <a href="{{ route('empresa.proyectos.edit', $proyecto->id) }}" class="btn-premium" style="justify-content: center; background: #3b82f6; padding: 14px 20px;">
                <i class="fas fa-edit"></i> Editar Proyecto
            </a>
            <a href="{{ route('empresa.proyectos.postulantes', $proyecto->id) }}" class="btn-premium" style="justify-content: center; padding: 14px 20px;">
                <i class="fas fa-users"></i> Ver Postulantes
            </a>
        </div>
    </div>
</div>
@endsection
