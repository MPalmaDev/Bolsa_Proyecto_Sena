@extends('layouts.dashboard')

@section('title', 'Explorar Proyectos - Inspírate SENA')
@section('page-title', 'Banco de Talento y Proyectos')

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

@php $breadcrumbs = [['label' => 'Inicio', 'url' => route('aprendiz.dashboard')], ['label' => 'Explorar Proyectos']]; @endphp
@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding-bottom: 60px;">
    
    <!-- SEARCH HERO SECTION -->
    <div style="background: linear-gradient(135deg, #0a1a15 0%, #1a2e28 100%); border-radius: 32px; padding: 60px 48px; margin-bottom: 40px; position: relative; overflow: hidden; box-shadow: 0 20px 50px -15px rgba(62,180,137,0.3);">
        <div style="position: absolute; right: -30px; bottom: -30px; font-size: 200px; color: rgba(62,180,137,0.06); transform: rotate(-15deg); pointer-events: none;">
            <i class="fas fa-search"></i>
        </div>
        
        <div style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <span style="background: #3eb489; color: white; padding: 8px 18px; border-radius: 40px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 8px 16px rgba(62,180,137,0.3);">
                    Banco de Proyectos
                </span>
            </div>
            <h2 style="color: white; font-size: 38px; font-weight: 900; margin-bottom: 12px;">Descubre tu Siguiente Desafío</h2>
            <p style="color: rgba(255,255,255,0.7); font-size: 16px; margin-bottom: 40px; max-width: 600px;">Explora cientos de proyectos de empresas líderes buscando el talento SENA que tú representas.</p>

            <!-- Search Bar -->
            <div style="position: relative; max-width: 700px;">
                <i class="fas fa-search" style="position: absolute; left: 24px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                <input type="text" id="ajax-search-input" value="{{ request('buscar') }}" placeholder="¿Qué quieres aprender hoy? (ej. React, Marketing, Diseño...)" style="width: 100%; padding: 20px 24px 20px 60px; border-radius: 20px; border: none; background: white; font-size: 15px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.1); outline: none;">
                <button type="button" id="ajax-search-btn" style="position: absolute; right: 12px; top: 12px; bottom: 12px; background: linear-gradient(135deg, #3eb489, #2d9d74); color: white; border: none; padding: 0 28px; border-radius: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 16px rgba(62,180,137,0.3);">
                    Buscar
                </button>
            </div>
        </div>
    </div> 

    <!-- CATEGORY PILLS -->
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; background: rgba(62,180,137,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-filter" style="color: #3eb489; font-size: 14px;"></i>
                </div>
                <h4 style="font-size: 14px; font-weight: 800; color: var(--text); margin: 0;">Filtrar por Área de Formación</h4>
                <span style="font-size: 12px; color: var(--text-light); font-weight: 500;">({{ count($categorias) }} áreas)</span>
            </div>
            @if(request()->anyFilled(['buscar', 'categoria']))
                <a href="{{ route('aprendiz.proyectos') }}" style="font-size: 12px; font-weight: 700; color: #ef4444; text-decoration: none; display: flex; align-items: center; gap: 6px; background: #fef2f2; padding: 8px 14px; border-radius: 20px;">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            @endif
        </div>
        
        @php
            $iconosCategoria = [
                'Electricidad y Energías' => 'fa-bolt',
                'Telecomunicaciones y Redes' => 'fa-wifi',
                'Mecánica y Mantenimiento Industrial' => 'fa-cogs',
                'Aviación y Sector Aeronáutico' => 'fa-plane',
                'Sistemas y Desarrollo de Software' => 'fa-laptop-code',
                'Automatización e Industria 4.0' => 'fa-robot',
                'Metalmecánica y Producción' => 'fa-industry',
                'Automotriz' => 'fa-car',
            ];
        @endphp
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <button type="button" class="ajax-cat-filter" data-categoria="" 
               style="padding: 10px 18px; background: {{ !request('categoria') ? 'linear-gradient(135deg, #3eb489, #2d9d74)' : 'white' }}; color: {{ !request('categoria') ? 'white' : '#64748b' }}; border: {{ !request('categoria') ? 'none' : '1.5px solid #e2e8f0' }}; border-radius: 30px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px; box-shadow: {{ !request('categoria') ? '0 4px 12px rgba(62,180,137,0.3)' : 'none' }};">
                <i class="fas fa-border-all"></i> Todos
            </button>
            @foreach($categorias as $cat)
                @php $icono = $iconosCategoria[$cat] ?? 'fa-folder'; @endphp
                <button type="button" class="ajax-cat-filter" data-categoria="{{ $cat }}"
                   style="padding: 10px 18px; background: {{ request('categoria') == $cat ? 'linear-gradient(135deg, #3eb489, #2d9d74)' : 'white' }}; color: {{ request('categoria') == $cat ? 'white' : '#1e293b' }}; border: {{ request('categoria') == $cat ? 'none' : '1.5px solid #e2e8f0' }}; border-radius: 30px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: {{ request('categoria') == $cat ? '0 4px 12px rgba(62,180,137,0.3)' : 'none' }};">
                    <i class="fas {{ $icono }}"></i> {{ $cat }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- RESULTS FEEDBACK -->
    <div style="margin-bottom: 24px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span id="ajax-total-count" style="font-size: 20px; font-weight: 800; color: var(--text);">{{ $proyectos->total() }}</span>
            <span style="font-size: 14px; color: var(--text-light); font-weight: 500;">Proyectos encontrados</span>
        </div>
        <span id="ajax-search-tag" style="display: {{ request('buscar') ? 'inline-flex' : 'none' }}; background: rgba(62,180,137,0.1); color: #3eb489; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; align-items: center; gap: 6px;">
            "{{ request('buscar') }}"
            <i class="fas fa-times" style="cursor:pointer;" onclick="document.getElementById('ajax-search-input').value=''; ajaxLoadProjects();"></i>
        </span>
    </div>

    <!-- PROJECTS GRID -->
    <div id="ajax-projects-grid">
        @if($proyectos->isEmpty())
            <div style="padding: 5rem 2rem; text-align: center; background: white; border-radius: 24px; border: 1px dashed rgba(62,180,137,0.2);">
                <div style="width: 100px; height: 100px; background: rgba(62,180,137,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                    <i class="fas {{ request('buscar') || request('categoria') ? 'fa-search' : 'fa-folder-open' }}" style="font-size: 40px; color: #3eb489;"></i>
                </div>
                @if(request('buscar') || request('categoria'))
                    <h3 style="font-size: 22px; font-weight: 800; color: var(--text); margin-bottom: 8px;">Sin resultados</h3>
                    <p style="color: var(--text-light); max-width: 400px; margin: 0 auto 24px;">No encontramos proyectos que coincidan con tu búsqueda "{{ request('buscar') ?? request('categoria') }}".</p>
                    <button type="button" onclick="ajaxClearFilters()" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #3eb489; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer;">
                        <i class="fas fa-rotate-left"></i> Limpiar filtros
                    </button>
                @else
                    <h3 style="font-size: 22px; font-weight: 800; color: var(--text); margin-bottom: 8px;">No hay proyectos disponibles</h3>
                    <p style="color: var(--text-light); max-width: 400px; margin: 0 auto;">Próximamente habrá nuevas oportunidades. Revisa más tarde.</p>
                @endif
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">
                @foreach($proyectos as $p)
                    <div style="background: white; border-radius: 24px; overflow: hidden; border: 1px solid rgba(62,180,137,0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 16px 40px rgba(62,180,137,0.15)'" onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                        <div style="height: 200px; position: relative;">
                            <img src="{{ $p->imagen_url }}" loading="lazy" alt="" style="width:100%; height:100%; object-fit:cover;">
                            <div style="position: absolute; top: 16px; left: 16px; background: linear-gradient(135deg, #3eb489, #2d9d74); color: white; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                {{ $p->categoria }}
                            </div>
                            @if($p->tipo_publicacion_badge)
                                @php $badge = $p->tipo_publicacion_badge; @endphp
                                <div style="position:absolute;top:16px;right:16px;background:{{ $badge['bg'] }};color:white;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:flex;align-items:center;gap:6px;">
                                    <i class="fas {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                                </div>
                            @endif
                        </div>
                        <div style="padding: 28px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                                <div style="width: 32px; height: 32px; background: rgba(62,180,137,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-building" style="color: #3eb489; font-size: 12px;"></i>
                                </div>
                                <span style="font-size: 13px; font-weight: 700; color: var(--text-light);">{{ $p->nombre }}</span>
                            </div>
                            <h3 style="font-size: 20px; font-weight: 800; color: var(--text); line-height: 1.4; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $p->titulo }}</h3>
                            <div style="display: flex; gap: 20px; margin-bottom: 24px; padding: 14px; background: rgba(62,180,137,0.03); border-radius: 14px; border: 1px solid rgba(62,180,137,0.08);">
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-light); font-weight: 600;"><i class="fas fa-clock" style="color: #f59e0b;"></i> {{ $p->duracion_estimada_dias }} días</div>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-light); font-weight: 600;"><i class="fas fa-users" style="color: #3eb489;"></i> {{ $p->postulados_count ?? 0 }} postulados</div>
                            </div>
                            <div style="margin-top: auto;">
                                @if(in_array($p->id, $postulados))
                                    <div style="background: linear-gradient(135deg, #3eb489, #2d9d74); color: white; padding: 14px; border-radius: 16px; text-align: center; font-size: 14px; font-weight: 700; box-shadow: 0 8px 20px rgba(62,180,137,0.3);">
                                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i> ¡Ya te has postulado!
                                    </div>
                                @else
                                    <button type="button" class="btn-postular-ajax" data-id="{{ $p->id }}" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #3eb489, #2d9d74); color: white; border: none; border-radius: 14px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 8px 20px rgba(62,180,137,0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 24px rgba(62,180,137,0.4)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 20px rgba(62,180,137,0.3)'">
                                        Postularme <i class="fas fa-paper-plane"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="ajax-pagination" style="margin-top: 50px; display: flex; justify-content: center;">
                {{ $proyectos->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentCategoria = '{{ request('categoria') }}';
let searchTimeout;

function ajaxLoadProjects() {
    const buscar = document.getElementById('ajax-search-input').value;
    const grid = document.getElementById('ajax-projects-grid');
    const countEl = document.getElementById('ajax-total-count');
    const searchTag = document.getElementById('ajax-search-tag');

    grid.innerHTML = '<div style="text-align:center;padding:60px;"><i class="fas fa-spinner fa-spin" style="font-size:32px;color:#3eb489;"></i><p style="margin-top:16px;font-weight:600;color:var(--text-light);">Buscando proyectos...</p></div>';

    axios.get('{{ route('aprendiz.proyectos.ajax') }}', {
        params: { buscar, categoria: currentCategoria }
    }).then(res => {
        grid.innerHTML = res.data.html;
        countEl.textContent = res.data.total;
        searchTag.style.display = buscar ? 'inline-flex' : 'none';
        if (searchTag.style.display !== 'none') {
            searchTag.innerHTML = '"' + buscar + '" <i class="fas fa-times" style="cursor:pointer;" onclick="document.getElementById(\'ajax-search-input\').value=\'\'; ajaxLoadProjects();"></i>';
        }
        bindPostularButtons();
        bindPaginationLinks();
    }).catch(() => {
        ajax.showToast('error', 'Error al cargar proyectos.');
    });
}

function bindPostularButtons() {
    document.querySelectorAll('.btn-postular-ajax').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            ajax.disableButton(this, 'Postulando...');
            axios.post('{{ route('aprendiz.postular', '') }}/' + id, {}, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(res => {
                if (res.data.success) {
                    this.outerHTML = '<div style="background:linear-gradient(135deg,#3eb489,#2d9d74);color:white;padding:14px;border-radius:16px;text-align:center;font-size:14px;font-weight:700;box-shadow:0 8px 20px rgba(62,180,137,0.3);"><i class="fas fa-check-circle" style="margin-right:8px;"></i> ' + res.data.message + '</div>';
                    ajax.showToast('success', res.data.message);
                } else {
                    ajax.enableButton(this);
                    ajax.showToast('error', res.data.message);
                }
            }).catch(err => {
                ajax.enableButton(this);
                const msg = err.response?.data?.message || 'Error al postular.';
                ajax.showToast('error', msg);
            });
        };
    });
}

function bindPaginationLinks() {
    document.querySelectorAll('#ajax-pagination a').forEach(a => {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const buscar = document.getElementById('ajax-search-input').value;
            const grid = document.getElementById('ajax-projects-grid');
            grid.innerHTML = '<div style="text-align:center;padding:60px;"><i class="fas fa-spinner fa-spin" style="font-size:32px;color:#3eb489;"></i></div>';

            axios.get(url.toString(), {
                params: { buscar, categoria: currentCategoria }
            }).then(res => {
                grid.innerHTML = res.data.html;
                bindPostularButtons();
                bindPaginationLinks();
            });
        });
    });
}

function ajaxClearFilters() {
    document.getElementById('ajax-search-input').value = '';
    currentCategoria = '';
    document.querySelectorAll('.ajax-cat-filter').forEach(b => {
        b.style.background = 'white';
        b.style.color = '#64748b';
        b.style.border = '1.5px solid #e2e8f0';
        b.style.boxShadow = 'none';
    });
    document.querySelector('.ajax-cat-filter[data-categoria=""]').style.background = 'linear-gradient(135deg, #3eb489, #2d9d74)';
    document.querySelector('.ajax-cat-filter[data-categoria=""]').style.color = 'white';
    document.querySelector('.ajax-cat-filter[data-categoria=""]').style.border = 'none';
    document.querySelector('.ajax-cat-filter[data-categoria=""]').style.boxShadow = '0 4px 12px rgba(62,180,137,0.3)';
    ajaxLoadProjects();
}

document.addEventListener('DOMContentLoaded', function() {
    bindPostularButtons();
    bindPaginationLinks();

    document.querySelectorAll('.ajax-cat-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.dataset.categoria;
            currentCategoria = cat;
            document.querySelectorAll('.ajax-cat-filter').forEach(b => {
                b.style.background = 'white';
                b.style.color = '#1e293b';
                b.style.border = '1.5px solid #e2e8f0';
                b.style.boxShadow = 'none';
            });
            if (cat === '') {
                document.querySelector('.ajax-cat-filter[data-categoria=""]').style.background = 'linear-gradient(135deg, #3eb489, #2d9d74)';
                document.querySelector('.ajax-cat-filter[data-categoria=""]').style.color = 'white';
                document.querySelector('.ajax-cat-filter[data-categoria=""]').style.border = 'none';
                document.querySelector('.ajax-cat-filter[data-categoria=""]').style.boxShadow = '0 4px 12px rgba(62,180,137,0.3)';
            } else {
                this.style.background = 'linear-gradient(135deg, #3eb489, #2d9d74)';
                this.style.color = 'white';
                this.style.border = 'none';
                this.style.boxShadow = '0 4px 12px rgba(62,180,137,0.3)';
            }
            ajaxLoadProjects();
        });
    });

    document.getElementById('ajax-search-btn').addEventListener('click', ajaxLoadProjects);
    document.getElementById('ajax-search-input').addEventListener('keyup', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(ajaxLoadProjects, 400);
    });
});
</script>
@endsection
