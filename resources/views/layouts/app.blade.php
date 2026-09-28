<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WebGIS Prediksi Banjir') - Kendari Barat</title>
    <meta name="description" content="Sistem WebGIS Prediksi Risiko Banjir Kecamatan Kendari Barat berbasis Machine Learning Ensemble Stacking">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg: #f8fafc;
            --bg-white: #ffffff;
            --bg-subtle: #f1f5f9;
            --border: #e2e8f0;
            --border-focus: #3b82f6;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --accent-blue: #1d4ed8;
            --accent-blue-light: #3b82f6;
            --accent-teal: #0891b2;
            --accent-green: #16a34a;
            --accent-red: #dc2626;
            --accent-orange: #ea580c;
            --accent-yellow: #ca8a04;
            --navbar-height: 60px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.10);
            --radius: 12px;
            --radius-sm: 8px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--navbar-height);
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 32px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .navbar-logo {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #1d4ed8, #0891b2);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            color: white;
            flex-shrink: 0;
        }

        .navbar-title {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .navbar-subtitle {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 2px;
            margin-left: auto;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 7px;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.18s;
        }

        .nav-link:hover { color: var(--text-primary); background: var(--bg-subtle); }

        .nav-link.active {
            color: var(--accent-blue);
            background: #eff6ff;
            font-weight: 600;
        }

        .nav-link i { font-size: 13px; }

        .navbar-divider {
            width: 1px;
            height: 22px;
            background: var(--border);
            margin: 0 8px;
        }

        /* ===== MAIN ===== */
        .main-content {
            margin-top: var(--navbar-height);
            min-height: calc(100vh - var(--navbar-height));
        }

        /* ===== CARDS ===== */
        .card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        /* ===== BADGE RISIKO ===== */
        .badge-tinggi     { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-sedang     { background: #fff7ed; color: #c2410c; border: 1px solid #fdba74; }
        .badge-rendah     { background: #fefce8; color: #a16207; border: 1px solid #fde047; }
        .badge-sangat-rendah { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-in { animation: fadeInUp 0.5s ease forwards; }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

        /* ===== FOOTER ===== */
        .site-footer {
            background: var(--bg-white);
            border-top: 1px solid var(--border);
            padding: 16px 24px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12.5px;
        }

        .site-footer a { color: var(--accent-blue-light); text-decoration: none; }
        .site-footer a:hover { text-decoration: underline; }
    </style>

    @stack('styles')
</head>
<body>

<nav class="navbar">
    <a href="{{ route('home') }}" class="navbar-brand">
        <div class="navbar-logo">🌊</div>
        <div>
            <div class="navbar-title">WebGIS Banjir</div>
            <div class="navbar-subtitle">KENDARI BARAT</div>
        </div>
    </a>

    <nav class="navbar-nav">
        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Beranda
        </a>
        <a href="{{ route('peta') }}" class="nav-link {{ request()->routeIs('peta') ? 'active' : '' }}">
            <i class="fas fa-map-marked-alt"></i> Peta
        </a>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Dashboard
        </a>
        <a href="{{ route('prediksi') }}" class="nav-link {{ request()->routeIs('prediksi') ? 'active' : '' }}">
            <i class="fas fa-brain"></i> Prediksi
        </a>
    </nav>
</nav>

<main class="main-content">
    @yield('content')
</main>

<footer class="site-footer">
    <p>WebGIS Prediksi Risiko Banjir - Kecamatan Kendari Barat &middot; Kota Kendari, Sulawesi Tenggara</p>
    <p style="margin-top:3px">Model: Ensemble Stacking (RF + XGBoost + LR) &middot; Data: 651 Titik Survei &middot;
       <a href="{{ route('dashboard') }}">Lihat Statistik</a>
    </p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@stack('scripts')
</body>
</html>
