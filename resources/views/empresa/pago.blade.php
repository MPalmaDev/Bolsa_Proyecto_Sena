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
                        <span style="color: var(--text-light);">Duracion</span>
                        <span>{{ $dias }} dias</span>
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

                {{-- Metodo de pago --}}
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 20px;">Metodo de Pago</h3>

                    <form method="POST" action="{{ route('empresa.proyectos.pago.procesar', $proyecto->id) }}" enctype="multipart/form-data" id="pago-form">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $tipo }}">
                        <input type="hidden" name="metodo" id="selected-metodo" value="wompi">

                        {{-- Wompi --}}
                        <div class="metodo-card selected" data-metodo="wompi" onclick="selectMetodo('wompi')" style="margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #6c63ff, #3b3b98); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 900;">W</div>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; font-weight: 800;">Wompi</div>
                                    <div style="font-size: 12px; color: var(--text-light); font-weight: 500;">Nequi, PSE, tarjetas — todo en uno</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                            </div>
                        </div>

                        {{-- MercadoPago --}}
                        <div class="metodo-card" data-metodo="mercadopago" onclick="selectMetodo('mercadopago')" style="margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #00b4ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 900;">MP</div>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; font-weight: 800;">MercadoPago</div>
                                    <div style="font-size: 12px; color: var(--text-light); font-weight: 500;">Tarjeta de credito, debito, PSE o efectivo</div>
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
                                    <div style="font-size: 14px; font-weight: 800;">Transferencia o Consignacion</div>
                                    <div style="font-size: 12px; color: var(--text-light); font-weight: 500;">Sube el comprobante y el admin lo validara</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                            </div>
                        </div>

                        {{-- Campos Wompi --}}
                        <div id="wompi-fields" style="display: block; margin-top: 20px;">
                            <div style="border: 2px solid #e0e0ff; border-radius: 16px; padding: 32px 24px; background: linear-gradient(135deg, #f5f3ff, #fff);">
                                <div style="text-align: center; margin-bottom: 24px;">
                                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #6c63ff, #3b3b98); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                        <span style="font-size: 28px; color: white; font-weight: 900;">W</span>
                                    </div>
                                    <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 4px;">Paga con Wompi</h4>
                                    <p style="font-size: 13px; color: var(--text-light); font-weight: 500;">
                                        Seras redirigido a Wompi para pagar <strong>${{ number_format($precio) }} COP</strong>
                                    </p>
                                </div>

                                <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
                                    <span style="background: white; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-mobile-alt" style="color: #e84393;"></i> Nequi
                                    </span>
                                    <span style="background: white; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-university" style="color: #3b82f6;"></i> PSE
                                    </span>
                                    <span style="background: white; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                                        <i class="fab fa-cc-visa" style="color: #1a1f71;"></i> Visa
                                    </span>
                                    <span style="background: white; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                                        <i class="fab fa-cc-mastercard" style="color: #eb001b;"></i> Mastercard
                                    </span>
                                </div>

                                @if(config('wompi.mode') === 'sandbox')
                                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 12px; color: #92400e;">
                                    <i class="fas fa-flask"></i> Sandbox: usa tarjeta <strong>4242 4242 4242 4242</strong>, cvv <strong>123</strong>, fecha futura.
                                </div>
                                @endif

                                <div style="display: flex; align-items: center; gap: 8px; padding: 12px; background: #f0fdf4; border-radius: 10px; font-size: 12px; color: #166534;">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Pago seguro via Wompi (Bancolombia)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Campos MercadoPago --}}
                        <div id="mp-fields" style="display: none; margin-top: 20px;">
                            @if(config('mercadopago.public_key'))
                            <div style="padding: 20px; background: #f8fafc; border-radius: 16px; text-align: center;">
                                <p style="font-size: 14px; color: var(--text-light); font-weight: 600; margin-bottom: 12px;">Al hacer clic en "Pagar", seras redirigido a MercadoPago</p>
                                <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 16px;">
                                    <span style="background: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0;"><i class="fab fa-cc-visa"></i> Visa</span>
                                    <span style="background: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0;"><i class="fab fa-cc-mastercard"></i> Mastercard</span>
                                    <span style="background: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0;"><i class="fas fa-university"></i> PSE</span>
                                </div>
                                <div id="wallet_container"></div>
                            </div>
                            @else
                            <div style="padding: 20px; background: #fffbeb; border-radius: 16px;">
                                <p style="font-size: 13px; font-weight: 600; color: #92400e;">
                                    <i class="fas fa-info-circle"></i> MercadoPago no configurado. Usa Wompi.
                                </p>
                            </div>
                            @endif
                        </div>

                        {{-- Campos Manual --}}
                        <div id="manual-fields" style="display: none; margin-top: 20px;">
                            <div style="border: 2px dashed #e2e8f0; border-radius: 16px; padding: 40px 24px; text-align: center; background: #f8fafc;">
                                <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                    <i class="fas fa-file-invoice" style="font-size: 24px; color: #3eb489;"></i>
                                </div>
                                <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 8px;">Sube tu comprobante de pago</h4>
                                <p style="font-size: 13px; color: var(--text-light); font-weight: 500; margin-bottom: 16px;">Realiza la transferencia por <strong>${{ number_format($precio) }} COP</strong> y sube el comprobante.</p>
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
    const mp = new MercadoPago('{{ config('mercadopago.public_key') }}', { locale: 'es-CO' });
    mp.bricks().create('wallet', 'wallet_container', {
        initialization: { preferenceId: '{{ $preferenceId ?? '' }}' },
        customization: { texts: { value: 'Pagar con MercadoPago' } }
    });
</script>
@endif
<script>
function selectMetodo(metodo) {
    document.querySelectorAll('.metodo-card').forEach(c => c.classList.remove('selected'));
    document.querySelector('.metodo-card[data-metodo="' + metodo + '"]').classList.add('selected');
    document.getElementById('selected-metodo').value = metodo;
    document.getElementById('wompi-fields').style.display = metodo === 'wompi' ? 'block' : 'none';
    document.getElementById('mp-fields').style.display = metodo === 'mercadopago' ? 'block' : 'none';
    document.getElementById('manual-fields').style.display = metodo === 'manual' ? 'block' : 'none';
    var btn = document.querySelector('.btn-premium');
    if (metodo === 'wompi') {
        btn.innerHTML = '<i class="fas fa-credit-card"></i> Pagar ${{ number_format($precio) }} COP con Wompi';
    } else if (metodo === 'mercadopago') {
        btn.innerHTML = '<i class="fas fa-lock"></i> Pagar ${{ number_format($precio) }} COP con MercadoPago';
    } else {
        btn.innerHTML = '<i class="fas fa-upload"></i> Enviar solicitud de pago';
    }
}
</script>
@endsection
