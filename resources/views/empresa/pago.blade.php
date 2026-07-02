@extends('layouts.dashboard')
@section('title', 'Pago - ' . ucfirst($tipo))
@section('page-title', 'Pago - Plan ' . ucfirst($tipo))

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

@section('styles')
    <style>
        .mp-card { border-radius: 20px; overflow: hidden; }
        .resumen-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; font-weight: 600; }
        .resumen-item:last-child { border-bottom: none; }
        .metodo-card { border: 2px solid #e2e8f0; border-radius: 16px; padding: 20px; cursor: pointer; transition: all 0.3s; }
        .metodo-card:hover { border-color: #3eb489; }
        .metodo-card.selected { border-color: #3eb489; background: rgba(62,180,137,0.03); }
    </style>
@endsection

@php
    $iconosTipo = ['destacado' => 'fa-star', 'patrocinado' => 'fa-crown'];
    $coloresTipo = ['destacado' => '#3b82f6', 'patrocinado' => '#f59e0b'];
@endphp

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding-bottom: 40px;">
    <div class="glass-card" style="padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #0a1a15, #1a2e28); padding: 40px 48px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: {{ $coloresTipo[$tipo] }}; display: flex; align-items: center; justify-content: center;">
                    <i class="fas {{ $iconosTipo[$tipo] }}" style="color: white; font-size: 20px;"></i>
                </div>
                <div>
                    <h2 style="color: white; font-size: 24px; font-weight: 900;">Plan {{ ucfirst($tipo) }}</h2>
                    <p style="color: rgba(255,255,255,0.6); font-size: 14px;">{{ $proyecto->titulo }}</p>
                </div>
            </div>
        </div>

        <div style="padding: 40px 48px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                {{-- Resumen --}}
                <div class="mp-card" style="background: #f8fafc; padding: 32px;">
                    <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 20px;">Resumen</h3>
                    <div class="resumen-item">
                        <span style="color: var(--text-light);">Proyecto</span>
                        <span>{{ $proyecto->titulo }}</span>
                    </div>
                    <div class="resumen-item">
                        <span style="color: var(--text-light);">Plan</span>
                        <span style="color: {{ $coloresTipo[$tipo] }};"><i class="fas {{ $iconosTipo[$tipo] }}"></i> {{ ucfirst($tipo) }}</span>
                    </div>
                    <div class="resumen-item">
                        <span style="color: var(--text-light);">Duración</span>
                        <span>{{ $dias }} días</span>
                    </div>
                    <div class="resumen-item" style="border-bottom: 2px dashed #e2e8f0; padding-bottom: 16px;">
                        <span style="color: var(--text-light);">Subtotal</span>
                        <span>${{ number_format($precio) }} COP</span>
                    </div>
                    <div class="resumen-item" style="font-size: 20px; padding-top: 16px;">
                        <span style="font-weight: 700;">Total</span>
                        <span style="font-weight: 900; color: {{ $coloresTipo[$tipo] }};">${{ number_format($precio) }} COP</span>
                    </div>
                </div>

                {{-- Método de pago --}}
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 20px;">Método de Pago</h3>

                    <form method="POST" action="{{ route('empresa.proyectos.pago.procesar', $proyecto->id) }}" enctype="multipart/form-data" id="pago-form">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $tipo }}">

                        {{-- MercadoPago --}}
                        <div class="metodo-card selected" data-metodo="mercadopago" onclick="selectMetodo('mercadopago')" style="margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #00b4ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 900;">MP</div>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; font-weight: 800;">MercadoPago</div>
                                    <div style="font-size: 12px; color: var(--text-light); font-weight: 500;">Tarjeta de crédito, débito, PSE o efectivo</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                            </div>
                        </div>

                        {{-- Nequi --}}
                        <div class="metodo-card" data-metodo="nequi" onclick="selectMetodo('nequi')" style="margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #e84393, #6c5ce7); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; font-weight: 800;">Nequi</div>
                                    <div style="font-size: 12px; color: var(--text-light); font-weight: 500;">Paga desde tu app Nequi — notificación push</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                            </div>
                        </div>

                        {{-- Manual --}}
                        <div class="metodo-card" data-metodo="manual" onclick="selectMetodo('manual')" style="margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 18px;">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; font-weight: 800;">Transferencia o Consignación</div>
                                    <div style="font-size: 12px; color: var(--text-light); font-weight: 500;">Sube el comprobante y el admin lo validará</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                            </div>
                        </div>

                        <input type="hidden" name="metodo" id="selected-metodo" value="mercadopago">

                        {{-- Campos MercadoPago --}}
                        <div id="mp-fields" style="display: block;">
                            @if(config('mercadopago.public_key'))
                            <div id="mp-checkout" style="margin-top: 20px;">
                                <div style="padding: 20px; background: #f8fafc; border-radius: 16px; text-align: center;">
                                    <p style="font-size: 14px; color: var(--text-light); font-weight: 600; margin-bottom: 12px;">Al hacer clic en "Pagar", serás redirigido a MercadoPago</p>
                                    <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 16px;">
                                        <span style="background: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0;"><i class="fab fa-cc-visa"></i> Visa</span>
                                        <span style="background: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0;"><i class="fab fa-cc-mastercard"></i> Mastercard</span>
                                        <span style="background: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0;"><i class="fas fa-university"></i> PSE</span>
                                        <span style="background: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0;"><i class="fas fa-building"></i> Efecty</span>
                                    </div>
                                    <div id="wallet_container"></div>
                                </div>
                            </div>
                            @else
                            <div style="padding: 20px; background: #fffbeb; border-radius: 16px; margin-top: 20px;">
                                <p style="font-size: 13px; font-weight: 600; color: #92400e;">
                                    <i class="fas fa-info-circle"></i> Pasarela de pago no configurada. Usa el método manual.
                                </p>
                            </div>
                            @endif
                        </div>

                        {{-- Campos Nequi --}}
                        <div id="nequi-fields" style="display: none; margin-top: 20px;">
                            <div style="border: 2px solid #f3e8ff; border-radius: 16px; padding: 32px 24px; background: linear-gradient(135deg, #faf5ff, #fff);">
                                <div style="text-align: center; margin-bottom: 24px;">
                                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #e84393, #6c5ce7); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                        <i class="fas fa-mobile-alt" style="font-size: 28px; color: white;"></i>
                                    </div>
                                    <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 4px;">Paga con Nequi</h4>
                                    <p style="font-size: 13px; color: var(--text-light); font-weight: 500;">
                                        Ingresa tu número Nequi y te llegará una notificación para aprobar el pago de <strong>${{ number_format($precio) }} COP</strong>
                                    </p>
                                </div>

                                @if($nequiSandbox)
                                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 12px; color: #92400e;">
                                    <i class="fas fa-flask"></i> Modo sandbox: usa <strong>{{ config('nequi.sandbox.phone') }}</strong> como número de prueba.
                                </div>
                                @endif

                                <div style="margin-bottom: 16px;">
                                    <label style="font-size: 13px; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">
                                        <i class="fas fa-phone"></i> Tu número Nequi
                                    </label>
                                    <input type="text" name="nequi_phone" id="nequi-phone-input"
                                           placeholder="Ej: 3001234567"
                                           style="width: 100%; padding: 14px 16px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 16px; font-weight: 600; letter-spacing: 1px; outline: none; transition: border-color 0.2s;"
                                           onfocus="this.style.borderColor='#6c5ce7'"
                                           onblur="this.style.borderColor='#e2e8f0'">
                                    <p style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Sin espacios ni guiones</p>
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px; padding: 12px; background: #f0fdf4; border-radius: 10px; font-size: 12px; color: #166534;">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Pago seguro vía Nequi Conecta</span>
                                </div>
                            </div>
                        </div>

                        {{-- Campos Manual --}}
                        <div id="manual-fields" style="display: none; margin-top: 20px;">
                            <div style="border: 2px dashed #e2e8f0; border-radius: 16px; padding: 40px 24px; text-align: center; background: #f8fafc;">
                                <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                    <i class="fas fa-file-invoice" style="font-size: 24px; color: #3eb489;"></i>
                                </div>
                                <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 8px;">Sube tu comprobante de pago</h4>
                                <p style="font-size: 13px; color: var(--text-light); font-weight: 500; margin-bottom: 16px;">Realiza la transferencia por <strong>${{ number_format($precio) }} COP</strong> a la cuenta que te indicará el administrador y sube el comprobante.</p>
                                <input type="file" name="comprobante" accept="image/*,.pdf" style="width: 100%; padding: 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: white; font-size: 14px;">
                            </div>
                        </div>

                        <button type="submit" class="btn-premium" style="width: 100%; padding: 16px; margin-top: 24px; font-size: 16px;">
                            <i class="fas fa-lock"></i> Pagar ${{ number_format($precio) }} COP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(config('mercadopago.public_key'))
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    const mp = new MercadoPago('{{ config('mercadopago.public_key') }}', {
        locale: 'es-CO'
    });
    mp.bricks().create('wallet', 'wallet_container', {
        initialization: {
            preferenceId: '{{ $preferenceId ?? '' }}',
        },
        customization: {
            texts: { value: 'Pagar con MercadoPago' }
        }
    });
</script>
@endif
<script>
function selectMetodo(metodo) {
    document.querySelectorAll('.metodo-card').forEach(c => c.classList.remove('selected'));
    document.querySelector('.metodo-card[data-metodo="' + metodo + '"]').classList.add('selected');
    document.getElementById('selected-metodo').value = metodo;
    document.getElementById('mp-fields').style.display = metodo === 'mercadopago' ? 'block' : 'none';
    document.getElementById('nequi-fields').style.display = metodo === 'nequi' ? 'block' : 'none';
    document.getElementById('manual-fields').style.display = metodo === 'manual' ? 'block' : 'none';
    var btn = document.querySelector('.btn-premium');
    if (metodo === 'nequi') {
        btn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pagar ${{ number_format($precio) }} COP con Nequi';
    } else if (metodo === 'mercadopago') {
        btn.innerHTML = '<i class="fas fa-lock"></i> Pagar ${{ number_format($precio) }} COP';
    } else {
        btn.innerHTML = '<i class="fas fa-upload"></i> Enviar solicitud de pago';
    }
}
</script>
@endsection
