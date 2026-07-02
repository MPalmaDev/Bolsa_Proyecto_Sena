<span class="nav-label">Administración</span>
<a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-th-large"></i> Principal
</a>
<a href="{{ route('admin.usuarios') }}" class="nav-item {{ request()->routeIs('admin.usuarios') ? 'active' : '' }}">
    <i class="fas fa-users"></i> Gestión Usuarios
</a>
<a href="{{ route('admin.empresas') }}" class="nav-item {{ request()->routeIs('admin.empresas') ? 'active' : '' }}">
    <i class="fas fa-building"></i> Empresas Aliadas
</a>
<a href="{{ route('admin.proyectos') }}" class="nav-item {{ request()->routeIs('admin.proyectos*') ? 'active' : '' }}">
    <i class="fas fa-project-diagram"></i> Banco Proyectos
</a>
<a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
    <i class="fas fa-chart-line"></i> Analítica
</a>
<span class="nav-label" style="margin-top: 16px;">Herramientas</span>
<a href="{{ route('admin.backup') }}" class="nav-item {{ request()->routeIs('admin.backup*') ? 'active' : '' }}">
    <i class="fas fa-database"></i> Backup
</a>
<a href="{{ route('admin.historial') }}" class="nav-item {{ request()->routeIs('admin.historial*') ? 'active' : '' }}">
    <i class="fas fa-clipboard-list"></i> Historial
</a>
<a href="{{ route('admin.pagos') }}" class="nav-item {{ request()->routeIs('admin.pagos*') ? 'active' : '' }}">
    <i class="fas fa-credit-card"></i> Pagos
</a>

<span class="nav-label" style="margin-top: 24px; display: flex; align-items: center; gap: 8px; color: var(--primary);">
    <i class="fas fa-headset" style="font-size: 10px;"></i> Soporte
</span>
<a href="{{ route('admin.mensajes.soporte') }}" class="nav-item {{ request()->routeIs('admin.mensajes.soporte*') ? 'active' : '' }}">
    <i class="fas fa-envelope"></i> Mensajes Soporte
</a>
