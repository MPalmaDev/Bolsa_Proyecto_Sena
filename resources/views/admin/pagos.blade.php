@extends('layouts.dashboard')
@section('title', 'Gestión de Pagos')
@section('page-title', 'Gestión de Pagos')

@section('sidebar-nav')
    @include('admin.partials.sidebar-nav')
@endsection

@section('styles')
<style>
    .pago-card { background: white; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 16px; transition: all 0.3s; }
    .pago-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .estado-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
</style>
@endsection

@section('content')
<div style="padding-bottom: 40px;">
    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card" style="background: linear-gradient(135deg, #0a1a15, #1a2e28); border: none;">
            <div class="stat-icon" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-credit-card"></i></div>
            <div>
                <div class="stat-num" style="color: white;">{{ $resumen['total'] }}</div>
                <div class="stat-label" style="color: rgba(255,255,255,0.6);">Total Pagos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-num" style="color: #f59e0b;">{{ $resumen['pendientes'] }}</div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #10b981;"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-num" style="color: #10b981;">{{ $resumen['confirmados'] }}</div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fee2e2; color: #ef4444;"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="stat-num" style="color: #ef4444;">{{ $resumen['rechazados'] }}</div>
                <div class="stat-label">Rechazados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;"><i class="fas fa-dollar-sign"></i></div>
            <div>
                <div class="stat-num" style="color: #3b82f6;">${{ number_format($resumen['ingresos']) }}</div>
                <div class="stat-label">Ingresos COP</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
        <a href="{{ route('admin.pagos') }}" class="btn" style="padding: 8px 16px; background: {{ !request('estado') ? '#3eb489' : 'white' }}; color: {{ !request('estado') ? 'white' : '#64748b' }}; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 700;">Todos</a>
        <a href="{{ route('admin.pagos', ['estado' => 'pendiente']) }}" class="btn" style="padding: 8px 16px; background: {{ request('estado') === 'pendiente' ? '#f59e0b' : 'white' }}; color: {{ request('estado') === 'pendiente' ? 'white' : '#64748b' }}; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 700;">Pendientes</a>
        <a href="{{ route('admin.pagos', ['estado' => 'confirmado']) }}" class="btn" style="padding: 8px 16px; background: {{ request('estado') === 'confirmado' ? '#10b981' : 'white' }}; color: {{ request('estado') === 'confirmado' ? 'white' : '#64748b' }}; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 700;">Confirmados</a>
        <a href="{{ route('admin.pagos', ['estado' => 'rechazado']) }}" class="btn" style="padding: 8px 16px; background: {{ request('estado') === 'rechazado' ? '#ef4444' : 'white' }}; color: {{ request('estado') === 'rechazado' ? 'white' : '#64748b' }}; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 700;">Rechazados</a>
    </div>

    {{-- Lista --}}
    @forelse($pagos as $pago)
        @php
            $estadosPago = [
                'pendiente' => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'fa-clock'],
                'confirmado' => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => 'fa-check-circle'],
                'rechazado' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => 'fa-times-circle'],
                'reembolsado' => ['bg' => '#e2e8f0', 'color' => '#475569', 'icon' => 'fa-rotate-left'],
            ];
            $estilo = $estadosPago[$pago->estado] ?? $estadosPago['pendiente'];
            $tiposColor = ['destacado' => '#3b82f6', 'patrocinado' => '#f59e0b'];
        @endphp
        <div class="pago-card">
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <span style="font-weight: 800; font-size: 16px;">{{ $pago->proyecto?->titulo ?? 'Proyecto eliminado' }}</span>
                        <span style="padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; color: white; background: {{ $tiposColor[$pago->tipo] ?? '#64748b' }};">
                            <i class="fas {{ $pago->tipo === 'patrocinado' ? 'fa-crown' : 'fa-star' }}"></i> {{ ucfirst($pago->tipo) }}
                        </span>
                    </div>
                    <div style="display: flex; gap: 20px; font-size: 13px; color: var(--text-light); font-weight: 600; flex-wrap: wrap;">
                        <span><i class="fas fa-building"></i> {{ $pago->empresa?->nombre ?? 'N/A' }}</span>
                        <span><i class="fas fa-tag"></i> ${{ number_format($pago->monto) }} COP</span>
                        <span><i class="fas fa-calendar"></i> {{ $pago->created_at->format('d M, Y H:i') }}</span>
                        @if($pago->metodo_pago === 'mercadopago')
                            <span><i class="fas fa-bolt"></i> MercadoPago</span>
                        @elseif($pago->metodo_pago === 'nequi')
                            <span><i class="fas fa-mobile-alt"></i> Nequi</span>
                        @else
                            <span><i class="fas fa-upload"></i> Manual</span>
                        @endif
                    </div>
                    @if($pago->mercadopago_id)
                        <div style="margin-top: 6px; font-size: 12px; color: var(--text-lighter);">MP ID: {{ $pago->mercadopago_id }}</div>
                    @endif
                </div>

                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                    <span class="estado-badge" style="background: {{ $estilo['bg'] }}; color: {{ $estilo['color'] }};">
                        <i class="fas {{ $estilo['icon'] }}"></i> {{ ucfirst($pago->estado) }}
                    </span>
                    @if($pago->estado === 'pendiente')
                        <div style="display: flex; gap: 8px;">
                            <button onclick="openConfirm('Confirmar pago', '¿Activar plan {{ ucfirst($pago->tipo) }} para {{ $pago->proyecto?->titulo }} por ${{ number_format($pago->monto) }} COP?', () => document.getElementById('confirm-form-{{ $pago->id }}').submit())" class="btn" style="padding: 8px 16px; background: #10b981; color: white; border: none; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer;">
                                <i class="fas fa-check"></i> Confirmar
                            </button>
                            <button onclick="openConfirm('Rechazar pago', '¿Rechazar este pago de ${{ number_format($pago->monto) }} COP?', () => document.getElementById('reject-form-{{ $pago->id }}').submit())" class="btn" style="padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer;">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                            <form id="confirm-form-{{ $pago->id }}" action="{{ route('admin.pagos.confirmar', $pago->id) }}" method="POST">@csrf</form>
                            <form id="reject-form-{{ $pago->id }}" action="{{ route('admin.pagos.rechazar', $pago->id) }}" method="POST">@csrf</form>
                        </div>
                    @elseif($pago->confirmador)
                        <div style="font-size: 12px; color: var(--text-lighter); font-weight: 500;">
                            Por: {{ $pago->confirmador->name }}
                            @if($pago->fecha_pago) · {{ $pago->fecha_pago->format('d M, Y') }}@endif
                        </div>
                    @endif
                    @if($pago->comprobante_url)
                        <a href="{{ asset('storage/' . $pago->comprobante_url) }}" target="_blank" style="font-size: 12px; color: #3eb489; font-weight: 600; text-decoration: underline;">
                            <i class="fas fa-download"></i> Ver comprobante
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 80px 20px;">
            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-credit-card" style="font-size: 32px; color: #94a3b8;"></i>
            </div>
            <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 8px;">Sin pagos registrados</h3>
            <p style="color: var(--text-light); font-weight: 500;">Aún no hay solicitudes de pago.</p>
        </div>
    @endforelse

    @if($pagos->hasPages())
        <div style="margin-top: 32px; display: flex; justify-content: center;">
            {{ $pagos->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
