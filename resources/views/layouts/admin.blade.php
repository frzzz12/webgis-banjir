<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - WebGIS Banjir Kendari Barat</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-bg:     #0d1117;
            --sidebar-border: rgba(255,255,255,.07);
            --sidebar-hover:  rgba(255,255,255,.06);
            --sidebar-active: rgba(26,115,232,.22);
            --accent:         #1a73e8;
            --accent-2:       #0d9488;
            --topbar-h:       60px;
            --sidebar-w:      250px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            overflow-x: hidden;
            color: #1e293b;
        }

        /* ─── SIDEBAR ─── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: var(--sidebar-bg);
            color: #e2e8f0;
            display: flex; flex-direction: column;
            box-shadow: 4px 0 24px rgba(0,0,0,.3);
            z-index: 1040; overflow-y: auto;
            transition: transform .3s;
        }
        .sidebar::-webkit-scrollbar { width: 3px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); }

        .sidebar-brand {
            display: flex; align-items: center; gap: 11px;
            padding: 20px 18px 16px;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }
        .sidebar-brand .brand-icon {
            width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: #fff;
        }
        .brand-name { font-size: 14px; font-weight: 700; color: #fff; line-height: 1.2; }
        .brand-sub  { font-size: 9.5px; color: #475569; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; margin-top: 1px; }

        .sidebar-section {
            padding: 14px 14px 5px;
            font-size: 9.5px; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase; color: #475569;
        }

        .sidebar-nav { padding: 0 8px; }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px; margin: 1px 0;
            font-size: 13px; font-weight: 500; color: #94a3b8;
            text-decoration: none; transition: all .18s;
        }
        .nav-link i { font-size: 14px; flex-shrink: 0; width: 18px; text-align: center; }
        .nav-link:hover { color: #e2e8f0; background: var(--sidebar-hover); }
        .nav-link.active {
            color: #fff; background: var(--sidebar-active);
            border-left: 3px solid var(--accent); padding-left: 9px;
            font-weight: 600;
        }
        .nav-link .badge-count {
            margin-left: auto; background: var(--accent);
            color: white; font-size: 10px; font-weight: 700;
            padding: 1px 7px; border-radius: 20px;
        }

        .sidebar-footer {
            margin-top: auto; padding: 12px 8px;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }
        .btn-logout {
            display: flex; align-items: center; gap: 9px;
            width: 100%; padding: 9px 12px; border-radius: 8px;
            background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.2);
            color: #f87171; font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all .18s;
        }
        .btn-logout:hover { background: rgba(239,68,68,.22); color: #fca5a5; }

        /* ─── TOPBAR ─── */
        .topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: var(--topbar-h);
            background: #fff; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px; z-index: 1030;
            box-shadow: 0 1px 10px rgba(0,0,0,.05);
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .topbar-title { font-size: 16px; font-weight: 700; color: #1e293b; }

        .sidebar-toggle {
            display: none; width: 34px; height: 34px;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
            align-items: center; justify-content: center;
            cursor: pointer; color: #64748b; font-size: 16px;
        }

        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .topbar-user {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 12px; border-radius: 8px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            font-size: 13px; color: #475569;
        }
        .topbar-user b { color: #1e293b; }
        .topbar-user i { color: var(--accent); }

        .topbar-view-map {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 8px;
            background: #eff6ff; border: 1px solid #bfdbfe;
            color: var(--accent); font-size: 12.5px; font-weight: 600;
            text-decoration: none; transition: all .15s;
        }
        .topbar-view-map:hover { background: #dbeafe; }

        /* ─── MAIN CONTENT ─── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }
        .page-body { padding: 28px; }

        /* ─── FLASH ─── */
        .flash-bar { padding: 18px 28px 0; }
        .alert {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 10px;
            font-size: 13.5px; font-weight: 500; margin-bottom: 8px;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-error   { background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; }
        .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { left: 0; }
            .sidebar-toggle { display: flex; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-water"></i></div>
        <div>
            <div class="brand-name">WebGIS Banjir</div>
            <div class="brand-sub">Admin Console</div>
        </div>
    </div>

    <div class="pt-2">
        <div class="sidebar-section">Main</div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        </nav>

        <div class="sidebar-section">Data</div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.data-banjir.index') }}"
               class="nav-link {{ request()->routeIs('admin.data-banjir*') ? 'active' : '' }}">
                <i class="fa-solid fa-location-dot"></i> Data Titik Banjir
            </a>
            <a href="{{ route('admin.prediksi') }}"
               class="nav-link {{ request()->routeIs('admin.prediksi') ? 'active' : '' }}">
                <i class="fa-solid fa-brain"></i> Prediksi Banjir
            </a>
        </nav>

        <div class="sidebar-section">Publik</div>
        <nav class="sidebar-nav">
            <a href="{{ url('/') }}" class="nav-link" target="_blank">
                <i class="fa-solid fa-map"></i> Lihat Peta Publik
                <i class="fa-solid fa-arrow-up-right-from-square" style="margin-left:auto;font-size:10px;opacity:.5;"></i>
            </a>
        </nav>
    </div>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}"
              onsubmit="return confirm('Yakin ingin keluar?')">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </button>
        </form>
    </div>
</aside>

<!-- ══ TOPBAR ══ -->
<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="topbar-right">
        <a href="{{ url('/') }}" class="topbar-view-map" target="_blank">
            <i class="fa-solid fa-map"></i> Peta Publik
        </a>
        <div class="topbar-user">
            <i class="fa-solid fa-circle-user"></i>
            <b>{{ Auth::user()->name }}</b>
        </div>
    </div>
</header>

<!-- ══ MAIN ══ -->
<div class="main-content">

    @if(session('success') || session('error') || session('warning'))
    <div class="flash-bar">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-xmark"></i>
            {{ session('error') }}
        </div>
        @endif
        @if(session('warning'))
        <div class="alert alert-warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ session('warning') }}
        </div>
        @endif
    </div>
    @endif

    <div class="page-body">
        @yield('content')
    </div>
</div>

<script>
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('show'));
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    // Auto-dismiss flash
    setTimeout(() => {
        document.querySelectorAll('.flash-bar .alert').forEach(el => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);
</script>
@stack('scripts')
</body>
</html>
