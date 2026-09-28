<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Risiko Banjir - Kendari Barat</title>
    <meta name="description" content="WebGIS interaktif prediksi risiko banjir Kecamatan Kendari Barat berbasis data spasial dan algoritma machine learning.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Turf.js - Spatial Analysis (Voronoi, Intersect, etc.) -->
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        /* ═══════════════════════════════════════════
           RESET & BASE
        ═══════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:      #1a73e8;
            --primary-lt:   #e8f0fe;
            --primary-dk:   #1557b0;
            --accent:       #0d9488;
            --surface:      #ffffff;
            --surface-2:    #f8fafc;
            --border:       #e2e8f0;
            --text-main:    #1e293b;
            --text-sub:     #64748b;
            --text-muted:   #94a3b8;
            --shadow-sm:    0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md:    0 4px 16px rgba(0,0,0,.10);
            --shadow-lg:    0 10px 40px rgba(0,0,0,.12);
            --radius:       12px;
            --radius-sm:    8px;
            --sidebar-w:    320px;
            --nav-h:        56px;

            /* Risiko warna */
            --r-sr:  #22c55e;
            --r-r:   #eab308;
            --r-s:   #f97316;
            --r-t:   #ef4444;
        }

        html, body { height: 100%; font-family: 'Inter', sans-serif; color: var(--text-main); background: var(--surface-2); }

        /* ═══════════════════════════════════════════
           TOPNAV
        ═══════════════════════════════════════════ */
        .topnav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            height: var(--nav-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex; align-items: center; gap: 0;
        }
        .topnav-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 0 20px;
            width: var(--sidebar-w);
            border-right: 1px solid var(--border);
            height: 100%;
            text-decoration: none;
        }
        .topnav-brand .brand-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 15px;
            flex-shrink: 0;
        }
        .topnav-brand .brand-text h1 {
            font-size: 13px; font-weight: 700; color: var(--text-main); line-height: 1.2;
        }
        .topnav-brand .brand-text p {
            font-size: 10.5px; color: var(--text-muted); font-weight: 400;
        }

        .topnav-menu {
            display: flex; align-items: center; gap: 2px;
            padding: 0 16px; flex: 1;
        }
        .topnav-menu a {
            display: flex; align-items: center; gap: 7px;
            padding: 6px 14px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            color: var(--text-sub);
            text-decoration: none;
            transition: all .18s ease;
        }
        .topnav-menu a:hover { background: var(--surface-2); color: var(--text-main); }
        .topnav-menu a.active {
            background: var(--primary-lt); color: var(--primary); font-weight: 600;
        }
        .topnav-menu a i { font-size: 13px; }

        .topnav-right {
            display: flex; align-items: center; gap: 10px; padding: 0 16px;
        }
        .badge-status {
            display: flex; align-items: center; gap: 6px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 20px; padding: 4px 12px;
            font-size: 11.5px; font-weight: 600; color: #15803d;
        }
        .badge-status .dot {
            width: 7px; height: 7px; border-radius: 50%; background: #22c55e;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .6; transform: scale(1.3); }
        }

        /* ═══════════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════════ */
        .layout {
            display: flex;
            height: 100vh;
            padding-top: var(--nav-h);
        }

        /* ═══════════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 18px 20px 14px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-header h2 {
            font-size: 14px; font-weight: 700; color: var(--text-main);
            display: flex; align-items: center; gap: 8px;
        }
        .sidebar-header h2 i { color: var(--primary); font-size: 14px; }
        .sidebar-header p {
            font-size: 11.5px; color: var(--text-muted); margin-top: 3px;
        }

        .sidebar-body { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 16px; }
        .sidebar-body::-webkit-scrollbar { width: 4px; }
        .sidebar-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        /* Card section */
        .s-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }
        .s-card-title {
            padding: 10px 14px;
            font-size: 11.5px; font-weight: 700; color: var(--text-sub);
            text-transform: uppercase; letter-spacing: .8px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            display: flex; align-items: center; gap: 7px;
        }
        .s-card-title i { color: var(--primary); }

        /* Layer Toggles */
        .layer-list { padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; }
        .layer-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 10px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all .15s ease;
        }
        .layer-item:hover { border-color: var(--primary); }
        .layer-item .layer-info {
            display: flex; align-items: center; gap: 10px;
        }
        .layer-dot {
            width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0;
        }
        .layer-name { font-size: 12.5px; font-weight: 500; color: var(--text-main); }
        .layer-sub  { font-size: 11px; color: var(--text-muted); }

        /* Toggle switch */
        .toggle-switch { position: relative; width: 36px; height: 20px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; inset: 0; cursor: pointer;
            background: #cbd5e1; border-radius: 20px;
            transition: .2s;
        }
        .toggle-slider::before {
            content: ''; position: absolute;
            width: 14px; height: 14px; border-radius: 50%;
            background: white; left: 3px; top: 3px;
            transition: .2s; box-shadow: var(--shadow-sm);
        }
        .toggle-switch input:checked + .toggle-slider { background: var(--primary); }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(16px); }

        /* Legend */
        .legend-list { padding: 10px 14px; display: flex; flex-direction: column; gap: 6px; }
        .legend-item {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 8px; border-radius: 6px;
            transition: background .15s;
        }
        .legend-item:hover { background: var(--surface); }
        .legend-dot {
            width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(0,0,0,.15);
        }
        .legend-label { font-size: 12px; font-weight: 500; color: var(--text-main); }
        .legend-count {
            margin-left: auto;
            font-size: 11px; font-weight: 600; color: var(--text-muted);
            background: var(--border); padding: 1px 7px; border-radius: 20px;
        }

        /* Stats row */
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 12px 14px; }
        .stat-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
        }
        .stat-val { font-size: 20px; font-weight: 700; color: var(--primary); line-height: 1; }
        .stat-lbl { font-size: 10.5px; color: var(--text-muted); margin-top: 3px; }

        /* Popup info selected */
        .selected-info { padding: 12px 14px; }
        .selected-info .no-select {
            text-align: center; padding: 20px;
            color: var(--text-muted); font-size: 12.5px;
        }
        .selected-info .no-select i { display: block; font-size: 28px; margin-bottom: 8px; opacity:.4; }
        .info-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 5px 0; border-bottom: 1px solid var(--border);
            font-size: 12px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .k { color: var(--text-sub); font-weight: 500; }
        .info-row .v { font-weight: 600; color: var(--text-main); text-align: right; max-width: 60%; }
        .risk-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 700;
        }

        /* ═══════════════════════════════════════════
           MAP CONTAINER
        ═══════════════════════════════════════════ */
        .map-wrapper { flex: 1; position: relative; overflow: hidden; }
        #map {
            width: 100%; height: 100%;
        }

        /* Map toolbar (floating controls) */
        .map-toolbar {
            position: absolute; top: 14px; right: 14px; z-index: 500;
            display: flex; flex-direction: column; gap: 6px;
        }
        .map-btn {
            width: 36px; height: 36px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--text-sub);
            font-size: 14px;
            transition: all .15s ease;
            box-shadow: var(--shadow-sm);
        }
        .map-btn:hover { background: var(--primary-lt); color: var(--primary); border-color: var(--primary); }
        .map-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* Basemap switcher (floating bottom-left) */
        .basemap-switcher {
            position: absolute; bottom: 24px; left: 14px; z-index: 500;
            display: flex; gap: 6px;
        }
        .basemap-btn {
            padding: 6px 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 11.5px; font-weight: 500;
            color: var(--text-sub); cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: all .15s ease;
        }
        .basemap-btn:hover { border-color: var(--primary); color: var(--primary); }
        .basemap-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* Scale badge */
        .map-scale {
            position: absolute; bottom: 24px; right: 14px; z-index: 500;
            background: rgba(255,255,255,.9); backdrop-filter: blur(4px);
            border: 1px solid var(--border);
            border-radius: 8px; padding: 5px 12px;
            font-size: 11px; color: var(--text-sub);
            box-shadow: var(--shadow-sm);
        }

        /* ═══════════════════════════════════════════
           LEAFLET CUSTOM STYLES
        ═══════════════════════════════════════════ */
        .leaflet-popup-content-wrapper {
            border-radius: var(--radius-sm) !important;
            box-shadow: var(--shadow-lg) !important;
            border: 1px solid var(--border);
            padding: 0 !important;
            overflow: hidden;
        }
        .leaflet-popup-content { margin: 0 !important; width: auto !important; }
        .leaflet-popup-tip-container { margin-top: -1px; }

        .custom-popup {
            font-family: 'Inter', sans-serif;
            min-width: 220px;
        }
        .custom-popup .popup-header {
            padding: 10px 14px;
            font-size: 12px; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
            border-bottom: 1px solid var(--border);
        }
        .custom-popup .popup-body { padding: 10px 14px; }
        .custom-popup .popup-row {
            display: flex; justify-content: space-between;
            font-size: 11.5px; padding: 3px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .custom-popup .popup-row:last-child { border-bottom: none; }
        .custom-popup .popup-row .pk { color: var(--text-muted); }
        .custom-popup .popup-row .pv { font-weight: 600; color: var(--text-main); }

        /* Leaflet control tweaks */
        .leaflet-control-zoom { border: 1px solid var(--border) !important; border-radius: var(--radius-sm) !important; box-shadow: var(--shadow-sm) !important; }
        .leaflet-control-zoom a { color: var(--text-sub) !important; font-weight: 600 !important; }
        .leaflet-control-zoom a:hover { background: var(--primary-lt) !important; color: var(--primary) !important; }

        /* Loading overlay */
        .map-loading {
            position: absolute; inset: 0; z-index: 600;
            background: rgba(255,255,255,.75); backdrop-filter: blur(2px);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 12px; transition: opacity .3s;
        }
        .map-loading.hidden { opacity: 0; pointer-events: none; }
        .spinner {
            width: 36px; height: 36px;
            border: 3px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .map-loading p { font-size: 13px; color: var(--text-sub); font-weight: 500; }

        /* Notification toast */
        .toast {
            position: fixed; bottom: 24px; left: calc(var(--sidebar-w) + 20px); z-index: 900;
            background: var(--text-main); color: white;
            padding: 10px 16px; border-radius: 8px;
            font-size: 12.5px; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
            box-shadow: var(--shadow-lg);
            transform: translateY(60px); opacity: 0;
            transition: all .3s cubic-bezier(.34,1.56,.64,1);
            pointer-events: none;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast i { font-size: 14px; }
        .toast.success { background: #166534; }
        .toast.error   { background: #991b1b; }
        .toast.info    { background: var(--primary); }
    </style>
</head>
<body>

<!-- ══ TOPNAV ══ -->
<nav class="topnav">
    <a href="{{ route('home') }}" class="topnav-brand">
        <div class="brand-icon"><i class="fa-solid fa-water"></i></div>
        <div class="brand-text">
            <h1>WebGIS Banjir</h1>
            <p>Kec. Kendari Barat</p>
        </div>
    </a>
    <div class="topnav-menu">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="{{ route('peta') }}" class="active"><i class="fa-solid fa-map"></i> Peta</a>
        <a href="{{ route('dashboard') }}"><i class="fa-solid fa-chart-bar"></i> Dashboard</a>
        <a href="{{ route('prediksi') }}"><i class="fa-solid fa-brain"></i> Prediksi</a>
    </div>
    <div class="topnav-right">
        <div class="badge-status">
            <span class="dot"></span>
            Data aktif
        </div>
    </div>
</nav>

<!-- ══ LAYOUT ══ -->
<div class="layout">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fa-solid fa-layer-group"></i> Panel Peta</h2>
            <p>Kelola layer dan lihat informasi spasial</p>
        </div>

        <div class="sidebar-body">

            <!-- Layer Control -->
            <div class="s-card">
                <div class="s-card-title"><i class="fa-solid fa-layers"></i> Layer Peta</div>
                <div class="layer-list">

                    <div class="layer-item" id="layer-batas-toggle" onclick="toggleLayer('batas')">
                        <div class="layer-info">
                            <div class="layer-dot" style="background:#1a73e8; border: 2px solid #1a73e8;"></div>
                            <div>
                                <div class="layer-name">Batas Wilayah</div>
                                <div class="layer-sub">Kec. Kendari Barat</div>
                            </div>
                        </div>
                        <label class="toggle-switch" onclick="event.stopPropagation()">
                            <input type="checkbox" id="toggle-batas" checked onchange="toggleLayer('batas')">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="layer-item" id="layer-titik-toggle" onclick="toggleLayer('titik')">
                        <div class="layer-info">
                            <div class="layer-dot" style="background:#ef4444;"></div>
                            <div>
                                <div class="layer-name">Titik Banjir</div>
                                <div class="layer-sub">Data kejadian historis</div>
                            </div>
                        </div>
                        <label class="toggle-switch" onclick="event.stopPropagation()">
                            <input type="checkbox" id="toggle-titik" checked onchange="toggleLayer('titik')">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="layer-item" id="layer-kelurahan-toggle" onclick="toggleLayer('kelurahan')">
                        <div class="layer-info">
                            <div class="layer-dot" style="background:linear-gradient(135deg,#ef4444,#22c55e);border-radius:3px;"></div>
                            <div>
                                <div class="layer-name">Segmentasi Kelurahan</div>
                                <div class="layer-sub">Risiko per kelurahan (klik polygon)</div>
                            </div>
                        </div>
                        <label class="toggle-switch" onclick="event.stopPropagation()">
                            <input type="checkbox" id="toggle-kelurahan" checked onchange="toggleLayer('kelurahan')">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Zona Risiko Sub-Kelurahan (Voronoi) -->
                    <div class="layer-item" id="layer-zona-toggle" onclick="toggleLayer('zona')">
                        <div class="layer-info">
                            <div class="layer-dot" style="background:linear-gradient(135deg,#ef4444 0%,#f97316 33%,#eab308 66%,#22c55e 100%);border-radius:3px;width:18px;height:18px;"></div>
                            <div>
                                <div class="layer-name">Zona Risiko Sub-Kelurahan</div>
                                <div class="layer-sub">Voronoi per titik - prediksi AI</div>
                            </div>
                        </div>
                        <div id="zona-loading-indicator" style="display:none;margin-right:6px;">
                            <i class="fa-solid fa-spinner fa-spin" style="color:#1a73e8;font-size:13px;"></i>
                        </div>
                        <label class="toggle-switch" onclick="event.stopPropagation()">
                            <input type="checkbox" id="toggle-zona" onchange="toggleLayer('zona')">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                </div>
            </div>

            <!-- Statistik -->
            <div class="s-card">
                <div class="s-card-title"><i class="fa-solid fa-chart-simple"></i> Statistik</div>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-val" id="stat-total">-</div>
                        <div class="stat-lbl">Total Titik</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-val" id="stat-tinggi" style="color:var(--r-t)">-</div>
                        <div class="stat-lbl">Risiko Tinggi</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-val" id="stat-sedang" style="color:var(--r-s)">-</div>
                        <div class="stat-lbl">Risiko Sedang</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-val" id="stat-rendah" style="color:var(--r-r)">-</div>
                        <div class="stat-lbl">Risiko Rendah</div>
                    </div>
                </div>
            </div>

            <!-- Legenda -->
            <div class="s-card">
                <div class="s-card-title"><i class="fa-solid fa-circle-info"></i> Legenda Risiko</div>
                <div class="legend-list">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--r-sr)"></div>
                        <span class="legend-label">Sangat Rendah</span>
                        <span class="legend-count" id="leg-sr">0</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--r-r)"></div>
                        <span class="legend-label">Rendah</span>
                        <span class="legend-count" id="leg-r">0</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--r-s)"></div>
                        <span class="legend-label">Sedang</span>
                        <span class="legend-count" id="leg-s">0</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--r-t)"></div>
                        <span class="legend-label">Tinggi</span>
                        <span class="legend-count" id="leg-t">0</span>
                    </div>
                </div>
            </div>

            <!-- Info Kelurahan Dipilih -->
            <div class="s-card" id="panel-kelurahan" style="display:none">
                <div class="s-card-title"><i class="fa-solid fa-city"></i> Info Kelurahan</div>
                <div id="kelurahan-info" style="padding:12px 14px;"></div>
            </div>

            <!-- Titik Dipilih -->
            <div class="s-card">
                <div class="s-card-title"><i class="fa-solid fa-map-pin"></i> Info Titik Dipilih</div>
                <div class="selected-info" id="selected-info">
                    <div class="no-select">
                        <i class="fa-regular fa-map"></i>
                        Klik titik atau kelurahan pada peta
                    </div>
                </div>
            </div>

        </div><!-- /sidebar-body -->
    </aside>

    <!-- ══ MAP ══ -->
    <div class="map-wrapper">
        <div id="map"></div>

        <!-- Loading overlay -->
        <div class="map-loading" id="map-loading">
            <div class="spinner"></div>
            <p>Memuat data peta…</p>
        </div>

        <!-- Floating map controls -->
        <div class="map-toolbar">
            <button class="map-btn" id="btn-fit" onclick="fitBounds()" title="Fit ke Kendari Barat">
                <i class="fa-solid fa-expand"></i>
            </button>
            <button class="map-btn" id="btn-fullscreen" onclick="toggleFullscreen()" title="Fullscreen">
                <i class="fa-solid fa-maximize"></i>
            </button>
        </div>

        <!-- Basemap switcher -->
        <div class="basemap-switcher">
            <button class="basemap-btn active" id="bm-osm" onclick="switchBasemap('osm')">
                <i class="fa-solid fa-map"></i> Standard
            </button>
            <button class="basemap-btn" id="bm-sat" onclick="switchBasemap('satellite')">
                <i class="fa-solid fa-satellite"></i> Satelit
            </button>
            <button class="basemap-btn" id="bm-topo" onclick="switchBasemap('topo')">
                <i class="fa-solid fa-mountain"></i> Topo
            </button>
        </div>

        <!-- Scale label -->
        <div class="map-scale" id="map-scale-lbl">
            <i class="fa-solid fa-location-crosshairs" style="margin-right:5px;"></i>
            <span id="coord-display">-</span>
        </div>
    </div>

</div><!-- /layout -->

<!-- Toast notification -->
<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-msg"></span></div>

<!-- ══ SCRIPTS ══ -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ══════════════════════════════════════════════
//  CONFIG
// ══════════════════════════════════════════════
const GEOJSON_BATAS      = '{{ route("api.geojson.batas") }}';
const GEOJSON_TITIK      = '{{ route("api.geojson.titik") }}';
const GEOJSON_KELURAHAN  = '{{ route("api.geojson.kelurahan") }}';
const GEOJSON_ZONA       = '{{ route("api.geojson.zona") }}';

const RISK_COLORS = {
    'sangat rendah': '#22c55e',
    'rendah':        '#eab308',
    'sedang':        '#f97316',
    'tinggi':        '#ef4444',
};

function getRiskColor(label) {
    const key = (label || '').toLowerCase().trim();
    return RISK_COLORS[key] || '#94a3b8';
}

// ══════════════════════════════════════════════
//  MAP INIT
// ══════════════════════════════════════════════
const map = L.map('map', {
    center: [-3.954, 122.551],
    zoom: 13,
    zoomControl: true,
    attributionControl: true,
});
map.attributionControl.setPrefix(false);


// ── Custom panes untuk z-order yang tepat ──────
// Kelurahan polygon di bawah titik-banjir marker
map.createPane('kelurahanPane');
map.createPane('batasPane');
map.createPane('titikPane');
map.getPane('kelurahanPane').style.zIndex = 200;
map.getPane('batasPane').style.zIndex     = 300;
map.getPane('titikPane').style.zIndex     = 400;
// titikPane tetap interactive - klik marker terdeteksi
// Klik di area kosong → jatuh ke kelurahanPane lewat map.on('click')
map.getPane('kelurahanPane').style.pointerEvents = 'auto';

// Simpan data kelurahan untuk point-in-polygon di map click
let kelurahanData = null;

// Basemaps
const basemaps = {
    osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri, Maxar, Earthstar Geographics',
        maxZoom: 19,
    }),
    topo: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenTopoMap contributors',
        maxZoom: 17,
    }),
};
basemaps.osm.addTo(map);
let activeBasemap = 'osm';

// Layers
let layerBatas      = null;
let layerTitik      = null;
let layerKelurahan  = null;
let layerZona       = null;   // Voronoi zona risiko sub-kelurahan
let zonaLoaded      = false;  // flag agar tidak load ulang
let activeKelurahan = null;
let boundsKendari   = null;

// Mode interaksi: 'titik' atau 'kelurahan'
let interaksiMode = 'kelurahan';

// Stats counters
const counts = { 'sangat rendah': 0, 'rendah': 0, 'sedang': 0, 'tinggi': 0 };

// ══════════════════════════════════════════════
//  LOAD BATAS WILAYAH
// ══════════════════════════════════════════════
async function loadBatas() {
    try {
        const res  = await fetch(GEOJSON_BATAS);
        const data = await res.json();

        layerBatas = L.geoJSON(data, {
            pane: 'batasPane',
            style: {
                color:       '#1a73e8',
                weight:      2.5,
                opacity:     0.9,
                fillColor:   '#1a73e8',
                fillOpacity: 0.04,
                dashArray:   '6, 4',
            },
            onEachFeature: (feature, layer) => {
                const kec = feature.properties?.KECAMATAN || 'Kendari Barat';
                layer.bindTooltip(`<b>${kec}</b>`, {
                    permanent: false, direction: 'center', className: 'leaflet-tooltip',
                });
            }
        }).addTo(map);

        boundsKendari = layerBatas.getBounds();
        map.fitBounds(boundsKendari.pad(0.15));
        showToast('Batas wilayah Kendari Barat berhasil dimuat', 'success');
    } catch (e) {
        showToast('Gagal memuat batas wilayah: ' + e.message, 'error');
    }
}

// ══════════════════════════════════════════════
//  LOAD KELURAHAN SEGMENTATION
// ══════════════════════════════════════════════
async function loadKelurahan() {
    try {
        const res  = await fetch(GEOJSON_KELURAHAN);
        const data = await res.json();
        kelurahanData = data;  // simpan untuk map.on('click')

        layerKelurahan = L.geoJSON(data, {
            pane: 'kelurahanPane',
            style: feature => kelurahanStyle(feature, false),
            onEachFeature: (feature, layer) => {
                const p = feature.properties;

                layer.bindTooltip(
                    `<b>${p.kelurahan}</b><br>Risiko dominan: <b>${capitalize(p.risiko_dominan)}</b>`,
                    { sticky: true, direction: 'top', className: 'leaflet-tooltip' }
                );

                layer.on('mouseover', function(e) {
                    this.setStyle({ weight: 3, fillOpacity: 0.60, color: '#1e293b' });
                });
                layer.on('mouseout', function(e) {
                    if (this !== activeKelurahan) {
                        layerKelurahan.resetStyle(this);
                    }
                });
                layer.on('click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    if (activeKelurahan) layerKelurahan.resetStyle(activeKelurahan);
                    activeKelurahan = this;
                    this.setStyle({ weight: 3.5, fillOpacity: 0.70, color: '#1e293b' });
                    showKelurahanInfo(p);
                });
            }
        }).addTo(map);

        // Klik di area kosong (bukan titik marker) → deteksi kelurahan via ray casting
        map.on('click', function(e) {
            if (!kelurahanData || !layerKelurahan) return;
            const lon = e.latlng.lng;
            const lat = e.latlng.lat;

            // Ray casting point-in-polygon
            function pipRay(px, py, ring) {
                let inside = false;
                const n = ring.length;
                let j = n - 1;
                for (let i = 0; i < n; i++) {
                    const xi = ring[i][0], yi = ring[i][1];
                    const xj = ring[j][0], yj = ring[j][1];
                    if (((yi > py) !== (yj > py)) &&
                        (px < (xj - xi) * (py - yi) / (yj - yi) + xi)) {
                        inside = !inside;
                    }
                    j = i;
                }
                return inside;
            }

            let matchedProps = null;
            for (const ft of kelurahanData.features) {
                const ring = ft.geometry.coordinates[0];
                if (pipRay(lon, lat, ring)) {
                    matchedProps = ft.properties;
                    break;
                }
            }

            if (!matchedProps) return;

            // Highlight layer yang sesuai
            layerKelurahan.eachLayer(function(layer) {
                const p = layer.feature.properties;
                if (p.kelurahan === matchedProps.kelurahan) {
                    if (activeKelurahan) layerKelurahan.resetStyle(activeKelurahan);
                    activeKelurahan = layer;
                    layer.setStyle({ weight: 3.5, fillOpacity: 0.70, color: '#1e293b' });
                    showKelurahanInfo(matchedProps);
                }
            });
        });


        showToast('Layer kelurahan berhasil dimuat', 'success');
    } catch (e) {
        showToast('Gagal memuat layer kelurahan: ' + e.message, 'error');
    }
}

function kelurahanStyle(feature, selected) {
    const p     = feature.properties;
    const color = p.warna || '#94a3b8';
    return {
        color:       selected ? '#1e293b' : '#fff',
        weight:      selected ? 3.5 : 1.8,
        opacity:     0.9,
        fillColor:   color,
        fillOpacity: selected ? 0.65 : 0.42,
    };
}

function showKelurahanInfo(p) {
    const panel = document.getElementById('panel-kelurahan');
    const box   = document.getElementById('kelurahan-info');
    panel.style.display = 'block';

    const riskColor = p.warna || '#94a3b8';
    const isPrediksi = p.sumber === 'prediksi_ml';

    const total = p.total_titik || 0;
    const pT  = total > 0 ? Math.round(p.tinggi        / total * 100) : 0;
    const pS  = total > 0 ? Math.round(p.sedang         / total * 100) : 0;
    const pR  = total > 0 ? Math.round(p.rendah         / total * 100) : 0;
    const pSR = total > 0 ? Math.round(p.sangat_rendah  / total * 100) : 0;

    let statusRawan = '';
    let statusWarna = '';
    if (['tinggi', 'sedang'].includes(p.risiko_dominan)) {
        statusRawan = 'Rawan Banjir';
        statusWarna = '#ef4444'; // Red
    } else {
        statusRawan = 'Tidak Rawan (Aman)';
        statusWarna = '#22c55e'; // Green
    }

    box.innerHTML = `
    <div style="margin-bottom:10px">
        <div style="font-size:15px;font-weight:800;color:#1e293b;margin-bottom:8px">
            ${p.kelurahan}
        </div>
        <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:8px;">
            <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;
                border-radius:20px;font-size:12px;font-weight:700;
                background:${statusWarna}22;color:${statusWarna};border:1px solid ${statusWarna}55; width: fit-content;">
                <i class="fa-solid ${statusRawan === 'Rawan Banjir' ? 'fa-triangle-exclamation' : 'fa-shield-check'}" style="font-size:11px"></i>
                Status: ${statusRawan}
            </span>
            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 11px;
                border-radius:20px;font-size:11px;font-weight:600;
                background:${riskColor}22;color:${riskColor};border:1px solid ${riskColor}44; width: fit-content;">
                <i class="fa-solid fa-circle" style="font-size:7px"></i>
                Risiko Dominan: ${isPrediksi ? 'Prediksi ML: ' : ''}${capitalize(p.risiko_dominan)}
            </span>
        </div>
        ${isPrediksi ? '<div style="font-size:10.5px;color:#7c3aed;margin-top:4px"><i class="fa-solid fa-robot"></i> Tidak ada data historis - menggunakan prediksi machine learning</div>' : ''}
    </div>
    ${total > 0 ? `
    <div class="info-row"><span class="k">Total Titik Data</span><span class="v" style="font-weight:800">${total}</span></div>
    <div class="info-row"><span class="k" style="color:#ef4444">● Tinggi</span><span class="v">${p.tinggi} <span style="color:#94a3b8">(${pT}%)</span></span></div>
    <div class="info-row"><span class="k" style="color:#f97316">● Sedang</span><span class="v">${p.sedang} <span style="color:#94a3b8">(${pS}%)</span></span></div>
    <div class="info-row"><span class="k" style="color:#eab308">● Rendah</span><span class="v">${p.rendah} <span style="color:#94a3b8">(${pR}%)</span></span></div>
    <div class="info-row"><span class="k" style="color:#22c55e">● Sangat Rendah</span><span class="v">${p.sangat_rendah} <span style="color:#94a3b8">(${pSR}%)</span></span></div>
    <div style="margin-top:8px;height:8px;border-radius:4px;overflow:hidden;display:flex">
        <div style="width:${pT}%;background:#ef4444"></div>
        <div style="width:${pS}%;background:#f97316"></div>
        <div style="width:${pR}%;background:#eab308"></div>
        <div style="width:${pSR}%;background:#22c55e"></div>
    </div>` : `
    <div class="info-row" style="padding:10px 0"><span class="k">Status</span>
        <span class="v" style="color:#7c3aed;font-size:11px">Prediksi ML (${p.prediksi_pct || 0}%)</span>
    </div>`}
    `;

    // Scroll sidebar ke panel kelurahan
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ══════════════════════════════════════════════
//  LOAD TITIK BANJIR
// ══════════════════════════════════════════════
async function loadTitikBanjir() {
    try {
        const res  = await fetch(GEOJSON_TITIK);
        const data = await res.json();

        Object.keys(counts).forEach(k => counts[k] = 0);
        let total = 0;

        layerTitik = L.geoJSON(data, {
            pointToLayer: (feature, latlng) => {
                const label = (feature.properties?.label || '').toLowerCase().trim();
                const color = getRiskColor(label);
                if (counts.hasOwnProperty(label)) counts[label]++;
                total++;

                // Titik berada di 'titikPane' (z=400), pointer-events=none
                // sehingga klik menembus ke kelurahan di bawahnya
                return L.circleMarker(latlng, {
                    pane:        'titikPane',
                    radius:      5,
                    fillColor:   color,
                    color:       'white',
                    weight:      1.5,
                    opacity:     1,
                    fillOpacity: 0.88,
                    interactive: true,   // titik tetap bisa diklik sendiri
                });
            },
            onEachFeature: (feature, layer) => {
                const p     = feature.properties || {};
                const label = (p.label || 'Tidak diketahui');
                const color = getRiskColor(label);

                layer.bindPopup(buildPopup(p, color), { maxWidth: 280 });
                layer.on('click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    showSelectedInfo(p, color);
                });
                layer.on('mouseover', function () {
                    this.setStyle({ radius: 9, weight: 2.5, color: '#1e293b' });
                    this.getElement() && (this.getElement().style.pointerEvents = 'auto');
                });
                layer.on('mouseout', function () {
                    this.setStyle({ radius: 5, weight: 1.5, color: 'white' });
                });
            }
        }).addTo(map);

        updateStats(total);
        showToast(`${total} titik data banjir berhasil dimuat`, 'success');
    } catch (e) {
        showToast('Gagal memuat titik banjir: ' + e.message, 'error');
    }
}

function buildPopup(p, color) {
    const labelCap = capitalize(p.label || 'Tidak diketahui');
    return `
    <div class="custom-popup">
        <div class="popup-header" style="color:${color}; border-bottom:2px solid ${color}20">
            <i class="fa-solid fa-circle-dot" style="color:${color}"></i>
            Risiko <strong>${labelCap}</strong>
        </div>
        <div class="popup-body">
            <div class="popup-row">
                <span class="pk">Riwayat Banjir</span>
                <span class="pv">${p.flood_hist || '-'}</span>
            </div>
            <div class="popup-row">
                <span class="pk">Kemiringan</span>
                <span class="pv">${p.slope ? Number(p.slope).toFixed(2) + '°' : '-'}</span>
            </div>
            <div class="popup-row">
                <span class="pk">Geologi</span>
                <span class="pv" style="font-size:10.5px">${truncate(p.geology || '-', 40)}</span>
            </div>
        </div>
    </div>`;
}

function showSelectedInfo(p, color) {
    const labelCap = capitalize(p.label || 'Tidak diketahui');
    const html = `
        <div class="info-row">
            <span class="k">Risiko</span>
            <span class="v">
                <span class="risk-badge" style="background:${color}20; color:${color}">
                    <i class="fa-solid fa-circle" style="font-size:8px"></i> ${labelCap}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="k">Riwayat Banjir</span>
            <span class="v">${p.flood_hist || '-'}</span>
        </div>
        <div class="info-row">
            <span class="k">Kemiringan</span>
            <span class="v">${p.slope ? Number(p.slope).toFixed(2) + '°' : '-'}</span>
        </div>
        <div class="info-row">
            <span class="k">Geologi</span>
            <span class="v" style="font-size:10.5px">${truncate(p.geology || '-', 35)}</span>
        </div>
    `;
    document.getElementById('selected-info').innerHTML = html;
}

function updateStats(total) {
    document.getElementById('stat-total').textContent  = total;
    document.getElementById('stat-tinggi').textContent = counts['tinggi'] || 0;
    document.getElementById('stat-sedang').textContent = counts['sedang'] || 0;
    document.getElementById('stat-rendah').textContent = counts['rendah'] || 0;
    document.getElementById('leg-sr').textContent      = counts['sangat rendah'] || 0;
    document.getElementById('leg-r').textContent       = counts['rendah'] || 0;
    document.getElementById('leg-s').textContent       = counts['sedang'] || 0;
    document.getElementById('leg-t').textContent       = counts['tinggi'] || 0;
}

// ══════════════════════════════════════════════
//  LAYER TOGGLE
// ══════════════════════════════════════════════
function toggleLayer(type) {
    if (type === 'batas') {
        const cb = document.getElementById('toggle-batas');
        if (layerBatas) {
            if (cb.checked) { map.addLayer(layerBatas); }
            else            { map.removeLayer(layerBatas); }
        }
    } else if (type === 'titik') {
        const cb = document.getElementById('toggle-titik');
        if (layerTitik) {
            if (cb.checked) { map.addLayer(layerTitik); }
            else            { map.removeLayer(layerTitik); }
        }
    } else if (type === 'kelurahan') {
        const cb = document.getElementById('toggle-kelurahan');
        if (layerKelurahan) {
            if (cb.checked) { map.addLayer(layerKelurahan); }
            else            { map.removeLayer(layerKelurahan); }
        }
    } else if (type === 'zona') {
        const cb = document.getElementById('toggle-zona');
        if (cb.checked) {
            // Load jika belum pernah di-load
            if (!zonaLoaded) {
                loadZonaRisiko();
            } else if (layerZona) {
                map.addLayer(layerZona);
            }
        } else {
            if (layerZona) { map.removeLayer(layerZona); }
        }
    }
}

// ══════════════════════════════════════════════
//  BASEMAP SWITCH
// ══════════════════════════════════════════════
function switchBasemap(name) {
    if (name === activeBasemap) return;
    map.removeLayer(basemaps[activeBasemap]);
    basemaps[name].addTo(map);
    basemaps[name].bringToBack();
    activeBasemap = name;

    ['osm', 'satellite', 'topo'].forEach(n => {
        document.getElementById('bm-' + n).classList.toggle('active', n === name);
    });
}

// ══════════════════════════════════════════════
//  FIT BOUNDS
// ══════════════════════════════════════════════
function fitBounds() {
    if (boundsKendari) {
        map.fitBounds(boundsKendari.pad(0.15));
    } else {
        map.setView([-3.954, 122.551], 13);
    }
}

// ══════════════════════════════════════════════
//  FULLSCREEN
// ══════════════════════════════════════════════
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.querySelector('.map-wrapper').requestFullscreen();
        document.getElementById('btn-fullscreen').innerHTML = '<i class="fa-solid fa-minimize"></i>';
        document.getElementById('btn-fullscreen').classList.add('active');
    } else {
        document.exitFullscreen();
        document.getElementById('btn-fullscreen').innerHTML = '<i class="fa-solid fa-maximize"></i>';
        document.getElementById('btn-fullscreen').classList.remove('active');
    }
}

// ══════════════════════════════════════════════
//  VORONOI ZONA RISIKO SUB-KELURAHAN
// ══════════════════════════════════════════════
async function loadZonaRisiko() {
    const indicator = document.getElementById('zona-loading-indicator');
    indicator.style.display = 'flex';

    try {
        // 1. Ambil titik-titik dari API (sudah ada label per titik)
        const [resTitik, resKel] = await Promise.all([
            fetch(GEOJSON_ZONA),
            fetch(GEOJSON_KELURAHAN),
        ]);
        const titikFC    = await resTitik.json();
        const kelurahanFC = await resKel.json();

        if (!titikFC.features || titikFC.features.length === 0) {
            showToast('Tidak ada data titik untuk zona risiko', 'error');
            document.getElementById('toggle-zona').checked = false;
            return;
        }

        // 2. Buat pane khusus untuk zona (antara kelurahan dan titik)
        if (!map.getPane('zonaPane')) {
            map.createPane('zonaPane');
            map.getPane('zonaPane').style.zIndex = 250;
            map.getPane('zonaPane').style.pointerEvents = 'none';
        }

        // 3. Bangun lookup polygon kelurahan dari GeoJSON
        const kelurahanMap = {};
        for (const ft of kelurahanFC.features) {
            const nama = ft.properties?.kelurahan;
            if (nama) kelurahanMap[nama] = ft;
        }

        // 4. Hitung bounding box seluruh titik + sedikit padding
        const bbox = turf.bbox(titikFC);
        const bboxPadded = [
            bbox[0] - 0.005, bbox[1] - 0.005,
            bbox[2] + 0.005, bbox[3] + 0.005,
        ];

        // 5. Buat Voronoi dari seluruh titik
        let voronoiFC;
        try {
            voronoiFC = turf.voronoi(titikFC, { bbox: bboxPadded });
        } catch (e) {
            showToast('Voronoi gagal: ' + e.message, 'error');
            return;
        }

        if (!voronoiFC || !voronoiFC.features) {
            showToast('Voronoi tidak menghasilkan polygon', 'error');
            return;
        }

        // 6. Gabungkan setiap cell Voronoi dengan properties titiknya
        //    Turf.voronoi() mengembalikan features dalam urutan yang sama dengan input points
        const voronoiWithLabel = voronoiFC.features.map((cell, i) => {
            const titikProps = titikFC.features[i]?.properties || {};
            return {
                ...cell,
                properties: {
                    ...titikProps,
                    label:    titikProps.label    || 'sangat rendah',
                    warna:    titikProps.warna    || '#22c55e',
                    kelurahan: titikProps.kelurahan || null,
                },
            };
        });

        // 7. Potong (intersect) setiap Voronoi cell dengan polygon kelurahannya
        const clippedFeatures = [];
        for (const cell of voronoiWithLabel) {
            const namaKel = cell.properties.kelurahan;
            if (!namaKel || !kelurahanMap[namaKel]) continue;

            try {
                const kelPoly = kelurahanMap[namaKel];
                // Tangani MultiPolygon vs Polygon
                const kelFeature = kelPoly.geometry.type === 'MultiPolygon'
                    ? turf.multiPolygon(kelPoly.geometry.coordinates)
                    : turf.polygon(kelPoly.geometry.coordinates);

                const clipped = turf.intersect(cell, kelFeature);
                if (clipped) {
                    clipped.properties = cell.properties;
                    clippedFeatures.push(clipped);
                }
            } catch (_) {
                // skip cell yang gagal (misal polygon tidak valid)
            }
        }

        if (clippedFeatures.length === 0) {
            showToast('Tidak ada zona yang berhasil diklip ke kelurahan', 'error');
            return;
        }

        // 8. Render ke Leaflet sebagai GeoJSON layer
        if (layerZona) { map.removeLayer(layerZona); }

        layerZona = L.geoJSON({ type: 'FeatureCollection', features: clippedFeatures }, {
            pane: 'zonaPane',
            style: feature => {
                const warna = feature.properties.warna || '#94a3b8';
                return {
                    color:       warna,
                    weight:      0.5,
                    opacity:     0.7,
                    fillColor:   warna,
                    fillOpacity: 0.45,
                };
            },
            onEachFeature: (feature, layer) => {
                const p     = feature.properties;
                const label = p.label   ? capitalize(p.label)   : '-';
                const kel   = p.kelurahan || '-';
                const warna = p.warna    || '#94a3b8';

                layer.bindTooltip(
                    `<div style="font-size:12px;line-height:1.6;">
                        <b style="color:${warna}">${label}</b><br>
                        <span style="color:#64748b">${kel}</span>
                     </div>`,
                    { sticky: true, direction: 'top', className: 'leaflet-tooltip' }
                );

                layer.on('mouseover', function () {
                    this.setStyle({ weight: 2, fillOpacity: 0.7 });
                });
                layer.on('mouseout', function () {
                    this.setStyle({ weight: 0.5, fillOpacity: 0.45 });
                });
                layer.on('click', function (e) {
                    L.DomEvent.stopPropagation(e);
                    const color = p.warna || '#94a3b8';
                    const html = `
                        <div class="info-row">
                            <span class="k">Zona Risiko</span>
                            <span class="v">
                                <span class="risk-badge" style="background:${color}20;color:${color}">
                                    <i class="fa-solid fa-circle" style="font-size:8px"></i> ${capitalize(p.label || '-')}
                                </span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="k">Kelurahan</span>
                            <span class="v">${p.kelurahan || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="k">Riwayat Banjir</span>
                            <span class="v">${p.flood_hist || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="k">Kemiringan Lereng</span>
                            <span class="v">${p.kemiringan || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="k">Penggunaan Lahan</span>
                            <span class="v">${p.penggunaan_lahan || '-'}</span>
                        </div>
                    `;
                    document.getElementById('selected-info').innerHTML = html;
                });
            }
        });

        layerZona.addTo(map);
        // Pastikan zona di bawah titik tapi di atas kelurahan
        if (layerKelurahan) layerKelurahan.bringToBack();

        zonaLoaded = true;
        showToast(`${clippedFeatures.length} zona risiko berhasil dimuat`, 'success');

    } catch (e) {
        showToast('Gagal memuat zona risiko: ' + e.message, 'error');
        document.getElementById('toggle-zona').checked = false;
    } finally {
        indicator.style.display = 'none';
    }
}

// ══════════════════════════════════════════════
//  COORDINATE DISPLAY
// ══════════════════════════════════════════════
map.on('mousemove', (e) => {
    const { lat, lng } = e.latlng;
    document.getElementById('coord-display').textContent =
        `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
});
map.on('mouseout', () => {
    document.getElementById('coord-display').textContent = '-';
});

// ══════════════════════════════════════════════
//  TOAST NOTIFICATION
// ══════════════════════════════════════════════
function showToast(msg, type = 'info') {
    const el = document.getElementById('toast');
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
    el.querySelector('i').className = `fa-solid ${icons[type] || icons.info}`;
    el.querySelector('#toast-msg').textContent = msg;
    el.className = `toast ${type} show`;
    setTimeout(() => { el.classList.remove('show'); }, 3500);
}

// ══════════════════════════════════════════════
//  HELPERS
// ══════════════════════════════════════════════
function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : str;
}
function truncate(str, n) {
    return str && str.length > n ? str.slice(0, n) + '…' : str;
}

// ══════════════════════════════════════════════
//  INIT
// ══════════════════════════════════════════════
(async function init() {
    const loading = document.getElementById('map-loading');
    try {
        await loadBatas();
        await loadKelurahan();   // pane z=200 (bawah)
        await loadTitikBanjir(); // pane z=400 (atas), pointer-events=none pada pane
        // Pastikan kelurahan di-render dulu, titik di atas tapi klik tetap bisa tembus
        if (layerKelurahan) layerKelurahan.bringToBack();
    } finally {
        loading.classList.add('hidden');
        setTimeout(() => { loading.style.display = 'none'; }, 400);
    }
})();
</script>
</body>
</html>
