<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#172554">
    <title>@yield('title', 'SIGI Calidad')</title>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('sigi-theme') || 'light';
        document.documentElement.dataset.fontSize = localStorage.getItem('sigi-font-size') || 'normal';
        document.documentElement.dataset.contrast = localStorage.getItem('sigi-contrast') || 'normal';
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <div class="app-shell">
        <aside class="sidebar" id="sidebar" aria-label="Navegación principal">
            <div class="brand">
                <img class="brand-mark" src="{{ asset('image.png') }}" alt="Escuela Profesional de Informática">
                <span><strong>SIGI</strong><small>Gestión de calidad</small></span>
            </div>

            <nav class="nav-list">
                <a href="{{ route('mapa-procesos.index') }}" @class(['nav-link', 'active' => request()->routeIs('mapa-procesos.*')])>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M10 6.5h4M17.5 10v4M10 17.5h4M6.5 10v4"/></svg>
                    <span>Mapa de procesos</span>
                </a>
                <a href="{{ route('quality-indicators.dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('quality-indicators.*')])>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-8m5 8V7"/><path d="m4 8 5-3 5 5 5-4"/></svg>
                    <span>Indicadores</span>
                </a>
                <a href="{{ route('documents.index') }}" @class(['nav-link', 'active' => request()->routeIs('documents.*')])>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></svg>
                    <span>Documentos</span>
                </a>
            </nav>

            <button type="button" class="sidebar-a11y-trigger" data-accessibility-open aria-haspopup="dialog">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="7" r="1.6" fill="currentColor" stroke="none"/><path d="M7 10h10M12 10v4m0 0-3 6m3-6 3 6"/></svg>
                <span>Accesibilidad</span>
            </button>
        </aside>

        <div class="page-shell">
            <header class="topbar">
                <button class="icon-btn mobile-menu" type="button" data-menu-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menú">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <div class="topbar-context">
                    <span class="eyebrow">Sistema institucional</span>
                    <strong>@yield('page-label', 'Panel de control')</strong>
                </div>
                <div class="topbar-actions">
                    <div class="profile-chip">
                        <span class="avatar">OC</span>
                        <span><strong>Oficina de Calidad</strong><small>Administrador</small></span>
                    </div>
                </div>
            </header>

            <main id="contenido" class="page-content" tabindex="-1">
                @if (session('success'))
                    <div class="alert success" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert error" role="alert">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/></svg>
                        <div><strong>Revisa la información.</strong><span>{{ $errors->first() }}</span></div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div class="mobile-overlay" data-menu-overlay hidden></div>

    <div class="simple-modal" data-accessibility-modal hidden>
        <div class="modal-backdrop" data-modal-close></div>
        <section class="compact-modal" role="dialog" aria-modal="true" aria-labelledby="a11y-modal-title">
            <header>
                <div><span class="eyebrow">Preferencias</span><h2 id="a11y-modal-title" class="mt-1">Accesibilidad</h2></div>
                <button class="icon-btn" type="button" data-modal-close aria-label="Cerrar">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </header>
            <div class="a11y-modal-body">
                <div class="a11y-row">
                    <div><strong>Tamaño de letra</strong><small data-font-size-label>Normal</small></div>
                    <div class="a11y-row-actions">
                        <button class="icon-btn" type="button" data-font-decrease aria-label="Disminuir tamaño de letra">A&minus;</button>
                        <button class="icon-btn" type="button" data-font-increase aria-label="Aumentar tamaño de letra">A+</button>
                    </div>
                </div>
                <div class="a11y-row">
                    <div><strong>Alto contraste</strong><small>Texto negro sobre fondo blanco, bordes más marcados</small></div>
                    <button class="icon-btn" type="button" data-contrast-toggle aria-label="Alternar alto contraste" aria-pressed="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 1 0 18z" fill="currentColor" stroke="none"/></svg>
                    </button>
                </div>
                <div class="a11y-row">
                    <div><strong>Tema de color</strong><small data-theme-label>Modo claro</small></div>
                    <button class="icon-btn" type="button" data-theme-toggle aria-label="Cambiar tema de color" aria-pressed="false">
                        <svg class="sun-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"/></svg>
                        <svg class="moon-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4 8.5 8.5 0 1 0 20 14.5Z"/></svg>
                    </button>
                </div>
            </div>
            <footer><button class="btn btn-ghost" type="button" data-modal-close>Cerrar</button></footer>
        </section>
    </div>

    @stack('modals')
</body>
</html>
