@extends('layouts.app')

@section('title', 'Inspírate SENA - Inicio')
@section('meta_description', 'Inspírate SENA - Bolsa de Proyectos. Conectamos talento con empresa en Colombia. Plataforma donde aprendices e instructores colaboran en proyectos reales.')
@section('og_title', 'Inspírate SENA - Conectamos Talento con Empresa')

@section('styles')
    @vitebuilt
        @vite(['resources/css/index.css'])
    @endvitebuilt
@endsection

@section('content')

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Inspírate SENA",
        "url": "{{ url('/') }}",
        "description": "Bolsa de Proyectos SENA. Conectamos talento con empresa en Colombia.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ url('/buscar') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Inspírate SENA",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('assets/logo.webp') }}",
        "description": "Plataforma que conecta talento de aprendices SENA con empresas en Colombia.",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Malambo",
            "addressRegion": "Atlántico",
            "addressCountry": "CO"
        }
    }
    </script>

    <section class="hero-section">
        <div class="hero-bg-blobs">
            <div class="hero-blob" style="top:-100px; right: -101px;"></div>
            <div class="hero-blob" style="bottom:-100px; left: -100px; background: rgba(59,130,246,0.1)"></div>
        </div>

        <div class="hero-layout">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-bolt"></i> Portal de Innovación
                </div>
                <h1 class="hero-title">
                    Conectamos <span>Talento</span> con<br><span>Empresa</span>
                </h1>
                <p class="hero-desc">
                    La plataforma definitiva donde aprendices e instructores colaboran en proyectos reales que transforman el ecosistema empresarial de Colombia. Conectamos el talento de los aprendices SENA con las necesidades de las empresas, creando oportunidades que impulsan el desarrollo profesional y la innovación en cada región del país.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        Comenzar Ahora <i class="fas fa-rocket"></i>
                    </a>
                    <a href="{{ route('nosotros') }}" class="btn btn-outline">
                        Ver Nosotros <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-image-wrapper">
                    <img src="{{ asset('assets/sena1.webp') }}" loading="lazy" alt="SENA" onerror="this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80'">
                </div>
            </div>
        </div>
    </section>

    <section class="index-stats">
        <div class="bento-stats">
            <div class="bento-stats-item">
                <div class="bento-stats-number">{{ $totalProyectos }}</div>
                <div class="bento-stats-label">Proyectos Activos</div>
            </div>
            <div class="bento-stats-item">
                <div class="bento-stats-number">{{ $totalInstructores }}</div>
                <div class="bento-stats-label">Instructores</div>
            </div>
            <div class="bento-stats-item">
                <div class="bento-stats-number">{{ $totalEmpresas }}</div>
                <div class="bento-stats-label">Empresas Aliadas</div>
            </div>
            <div class="bento-stats-item">
                <div class="bento-stats-number">{{ $totalAprendices }}</div>
                <div class="bento-stats-label">Aprendices</div>
            </div>
        </div>
    </section>

    <section class="bento-grid">
        <div class="bento-item empresas">
            <div class="bento-icon"><i class="fas fa-building"></i></div>
            <h3>Empresas</h3>
            <p>Encuentra soluciones innovadoras para tus desafíos técnicos encargando proyectos a equipos de aprendices calificados. Cada empresa encuentra en nuestra plataforma el talento fresco y capacitado que necesita para crecer y resolver retos reales de la industria colombiana.</p>
            <a href="{{ route('registro.empresa') }}" class="btn">
                Registrar empresa <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bento-item instructores">
            <div class="bento-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <h3>Instructores</h3>
            <p>Lidera el desarrollo de competencias prácticas guiando a los aprendices en la ejecución de proyectos de alto valor. Los instructores son el puente que conecta el talento emergente con las demandas reales de cada empresa, asegurando resultados de calidad.</p>
            <a href="{{ route('registro.instructor') }}" class="btn">
                Unirme como guía <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bento-item aprendices">
            <div class="bento-icon"><i class="fas fa-user-graduate"></i></div>
            <h3>Aprendices</h3>
            <p>Participa en retos reales, adquiere experiencia certificable y conecta directamente con empresas aliadas. Cada aprendiz demuestra su talento en proyectos auténticos, construye su portafolio profesional y establece conexiones valiosas con el mundo empresarial.</p>
            <a href="{{ route('registro.aprendiz') }}" class="btn">
                Postular talento <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    @if($proyectosPatrocinados->isNotEmpty())
    <section class="bento-grid">
        <div style="grid-column: 1 / -1;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-crown"></i>
                </div>
                <h2 style="font-size: 24px; font-weight: 900; color: var(--text);">Proyectos <span style="color: #f59e0b;">Patrocinados</span></h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                @foreach($proyectosPatrocinados as $p)
                <div style="background: white; border-radius: 20px; overflow: hidden; border: 2px solid rgba(245,158,11,0.2); box-shadow: 0 8px 24px rgba(245,158,11,0.1);">
                    <div style="height: 160px; position: relative; background: #f8fafc;">
                        <img src="{{ $p->imagen_url }}" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
                        <div style="position: absolute; top: 12px; right: 12px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-crown"></i> Patrocinado
                        </div>
                        <div style="position: absolute; bottom: 12px; left: 12px; background: rgba(0,0,0,0.6); color: white; padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 600;">
                            {{ $p->categoria }}
                        </div>
                    </div>
                    <div style="padding: 20px;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-light); margin-bottom: 6px;">{{ $p->empresa?->nombre }}</div>
                        <h4 style="font-size: 16px; font-weight: 800; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $p->titulo }}</h4>
                        <div style="display: flex; gap: 12px; font-size: 11px; color: var(--text-light); font-weight: 600;">
                            <span><i class="fas fa-clock" style="color: #f59e0b;"></i> {{ $p->duracion_estimada_dias }} días</span>
                            <span><i class="fas fa-building"></i> {{ $p->empresa?->nombre }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="cta-section">
        <div class="cta-content">
            <h2>¿Listo para transformar el futuro?</h2>
            <p>Únete hoy a la mayor comunidad de innovación técnica y comienza a generar valor real en la industria. Empresas, instructores y aprendices trabajando juntos para conectar talento con oportunidades que transforman el futuro laboral de Colombia. La bolsa de proyectos donde el talento SENA encuentra a la empresa ideal y cada proyecto se convierte en una experiencia de crecimiento profesional.</p>
            <a href="{{ route('login') }}" class="btn">
                Comenzar Ahora <i class="fas fa-rocket"></i>
            </a>
        </div>
    </section>
@endsection
