@extends('layouts.app')
@section('title', 'Dashboard Statistik')

@push('styles')
<style>
    .dash-page { padding: 32px 24px; max-width: 1400px; margin: 0 auto; }

    .dash-header { margin-bottom: 28px; }
    .dash-header h1 { font-family: "Outfit", sans-serif; font-size: 28px; font-weight: 800; margin-bottom: 4px; }
    .dash-header p { color: var(--text-secondary); font-size: 14px; }

    /* STAT CARDS */
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }

    .stat-big {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-big::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 80px; height: 80px;
        border-radius: 50%;
        transform: translate(30%, -30%);
        opacity: 0.15;
    }

    .stat-big:hover { border-color: var(--border-hover); transform: translateY(-3px); box-shadow: var(--shadow-glow); }

    .stat-big-icon { font-size: 28px; margin-bottom: 12px; }
    .stat-big-val { font-family: "Outfit", sans-serif; font-size: 36px; font-weight: 800; margin-bottom: 4px; }
    .stat-big-lbl { font-size: 13px; color: var(--text-muted); font-weight: 500; }
    .stat-big-sub { font-size: 11px; color: var(--text-muted); margin-top: 6px; }

    /* CHARTS GRID */
    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }

    .chart-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }

    .chart-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .chart-subtitle { font-size: 12px; color: var(--text-muted); margin-bottom: 20px; }

    .chart-wrap { position: relative; height: 250px; }

    /* FEATURE IMPORTANCE */
    .feature-bar-wrap { margin-bottom: 8px; }
    .feature-bar-label {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 4px;
    }
    .feature-bar-label span:first-child { color: var(--text-primary); font-weight: 500; }
    .feature-bar-label span:last-child { color: var(--text-muted); }
    .feature-bar-track { height: 8px; background: rgba(255,255,255,0.06); border-radius: 4px; overflow: hidden; }
    .feature-bar-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }

    /* DATA TABLE */
    .table-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .table-title { font-size: 15px; font-weight: 700; }

    .search-input {
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px 14px;
        color: var(--text-primary);
        font-size: 13px;
        font-family: "Inter", sans-serif;
        width: 220px;
        outline: none;
        transition: border-color 0.2s;
    }

    .search-input:focus { border-color: var(--accent-blue); }
    .search-input::placeholder { color: var(--text-muted); }

    table { width: 100%; border-collapse: collapse; }
    th {
        background: rgba(255,255,255,0.03);
        padding: 10px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--border);
    }
    td {
        padding: 10px 16px;
        font-size: 13px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
    }
    tr:hover td { background: rgba(255,255,255,0.02); }
    tr:last-child td { border: none; }

    .risiko-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .table-footer {
        padding: 12px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: var(--text-muted);
    }

    .page-btn {
        padding: 5px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-primary);
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s;
    }
    .page-btn:hover { border-color: var(--accent-blue); }
    .page-btn.active { background: var(--accent-blue); border-color: var(--accent-blue); color: white; }

    @media (max-width: 900px) {
        .stat-grid { grid-template-columns: repeat(2, 1fr); }
        .charts-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="dash-page">
    <div class="dash-header">
        <h1>📊 Dashboard Statistik</h1>
        <p>Analisis distribusi risiko banjir &bull; 651 titik survei &bull; Kecamatan Kendari Barat</p>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-grid">
        <div class="stat-big" style="border-color:rgba(59,130,246,0.2)">
            <div class="stat-big-icon">📍</div>
            <div class="stat-big-val" style="color:var(--accent-blue)">651</div>
            <div class="stat-big-lbl">Total Titik Survei</div>
            <div class="stat-big-sub">12 parameter per titik</div>
        </div>
        <div class="stat-big" style="border-color:rgba(239,68,68,0.2)">
            <div class="stat-big-icon">🔴</div>
            <div class="stat-big-val" style="color:var(--accent-red)">355</div>
            <div class="stat-big-lbl">Risiko Tinggi</div>
            <div class="stat-big-sub">54.5% dari total titik</div>
        </div>
        <div class="stat-big" style="border-color:rgba(16,185,129,0.2)">
            <div class="stat-big-icon">🟢</div>
            <div class="stat-big-val" style="color:var(--accent-green)">246</div>
            <div class="stat-big-lbl">Risiko Sangat Rendah</div>
            <div class="stat-big-sub">37.8% dari total titik</div>
        </div>
        <div class="stat-big" style="border-color:rgba(6,182,212,0.2)">
            <div class="stat-big-icon">🤖</div>
            <div class="stat-big-val" style="color:var(--accent-cyan)">100%</div>
            <div class="stat-big-lbl">Akurasi Model</div>
            <div class="stat-big-sub">F1-Score: 1.0000</div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="charts-grid">

        <div class="chart-card">
            <div class="chart-title">Distribusi Risiko Banjir</div>
            <div class="chart-subtitle">Proporsi kelas risiko dari 651 titik survei</div>
            <div class="chart-wrap">
                <canvas id="chartPie"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">Jumlah Titik per Kelas Risiko</div>
            <div class="chart-subtitle">Perbandingan absolut antar kelas</div>
            <div class="chart-wrap">
                <canvas id="chartBar"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">Feature Importance (Top 5)</div>
            <div class="chart-subtitle">Kontribusi fitur terhadap prediksi model</div>
            <div style="margin-top:8px">
                <div class="feature-bar-wrap">
                    <div class="feature-bar-label"><span>skor_total</span><span>34.4%</span></div>
                    <div class="feature-bar-track"><div class="feature-bar-fill" style="width:34.4%;background:linear-gradient(90deg,#3b82f6,#06b6d4)"></div></div>
                </div>
                <div class="feature-bar-wrap">
                    <div class="feature-bar-label"><span>slope</span><span>27.4%</span></div>
                    <div class="feature-bar-track"><div class="feature-bar-fill" style="width:27.4%;background:linear-gradient(90deg,#8b5cf6,#3b82f6)"></div></div>
                </div>
                <div class="feature-bar-wrap">
                    <div class="feature-bar-label"><span>geologi</span><span>23.9%</span></div>
                    <div class="feature-bar-track"><div class="feature-bar-fill" style="width:23.9%;background:linear-gradient(90deg,#06b6d4,#10b981)"></div></div>
                </div>
                <div class="feature-bar-wrap">
                    <div class="feature-bar-label"><span>riwayat_banjir</span><span>10.5%</span></div>
                    <div class="feature-bar-track"><div class="feature-bar-fill" style="width:10.5%;background:linear-gradient(90deg,#f59e0b,#ef4444)"></div></div>
                </div>
                <div class="feature-bar-wrap">
                    <div class="feature-bar-label"><span>kelas_lereng</span><span>3.7%</span></div>
                    <div class="feature-bar-track"><div class="feature-bar-fill" style="width:3.7%;background:linear-gradient(90deg,#10b981,#06b6d4)"></div></div>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">Performa Model</div>
            <div class="chart-subtitle">Perbandingan 3 model yang digunakan</div>
            <div class="chart-wrap">
                <canvas id="chartModel"></canvas>
            </div>
        </div>

    </div>

    <!-- DATA TABLE -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">📋 Data Titik Survei</div>
            <input type="text" id="searchInput" class="search-input" placeholder="🔍 Cari titik...">
        </div>
        <div style="overflow-x:auto">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Koordinat</th>
                        <th>Curah Hujan</th>
                        <th>Kemiringan</th>
                        <th>Jenis Tanah</th>
                        <th>Penggunaan Lahan</th>
                        <th>Risiko Prediksi</th>
                        <th>Probabilitas</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span id="tableInfo">-</span>
            <div id="pageBtns" style="display:flex;gap:6px"></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ===== PIE CHART =====
new Chart(document.getElementById('chartPie'), {
    type: 'doughnut',
    data: {
        labels: ['Tinggi', 'Sangat Rendah', 'Sedang', 'Rendah'],
        datasets: [{
            data: [355, 246, 26, 24],
            backgroundColor: ['#ef4444','#10b981','#f59e0b','#eab308'],
            borderColor: '#111827',
            borderWidth: 3,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 16, font: { size: 12 } } },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` ${ctx.label}: ${ctx.raw} titik (${((ctx.raw/651)*100).toFixed(1)}%)`
                }
            }
        }
    }
});

// ===== BAR CHART =====
new Chart(document.getElementById('chartBar'), {
    type: 'bar',
    data: {
        labels: ['Tinggi', 'Sangat Rendah', 'Sedang', 'Rendah'],
        datasets: [{
            label: 'Jumlah Titik',
            data: [355, 246, 26, 24],
            backgroundColor: ['rgba(239,68,68,0.7)','rgba(16,185,129,0.7)','rgba(245,158,11,0.7)','rgba(234,179,8,0.7)'],
            borderColor: ['#ef4444','#10b981','#f59e0b','#eab308'],
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#94a3b8' } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#94a3b8' } }
        }
    }
});

// ===== MODEL CHART =====
new Chart(document.getElementById('chartModel'), {
    type: 'bar',
    data: {
        labels: ['Random Forest', 'XGBoost', 'Stacking (Final)'],
        datasets: [
            { label: 'Accuracy', data: [1.0, 1.0, 1.0], backgroundColor: 'rgba(59,130,246,0.7)', borderColor: '#3b82f6', borderWidth: 2, borderRadius: 6 },
            { label: 'F1-Score', data: [1.0, 1.0, 1.0], backgroundColor: 'rgba(6,182,212,0.7)', borderColor: '#06b6d4', borderWidth: 2, borderRadius: 6 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#94a3b8' } } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#94a3b8' } },
            y: { min: 0, max: 1.1, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#94a3b8', callback: v => (v*100).toFixed(0)+'%' } }
        }
    }
});

// ===== DATA TABLE =====
let allData = [];
let page = 1;
const perPage = 15;

const risikoStyle = {
    'Tinggi':        'background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)',
    'Sedang':        'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)',
    'Rendah':        'background:rgba(234,179,8,0.15);color:#eab308;border:1px solid rgba(234,179,8,0.3)',
    'Sangat Rendah': 'background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)'
};

fetch('/geojson/titik_banjir_prediksi.geojson')
    .then(r => r.json())
    .then(data => {
        allData = data.features;
        renderTable(allData);
    });

function renderTable(data) {
    const start = (page - 1) * perPage;
    const slice = data.slice(start, start + perPage);
    const tbody = document.getElementById('tableBody');

    tbody.innerHTML = slice.map((f, i) => {
        const p = f.properties;
        const coords = f.geometry.coordinates;
        const style = risikoStyle[p.risiko_prediksi] || '';
        return `<tr>
            <td style="color:var(--text-muted)">${start + i + 1}</td>
            <td style="font-size:11px;font-family:monospace">${coords[1].toFixed(4)}, ${coords[0].toFixed(4)}</td>
            <td>${p.curah_hujan ? p.curah_hujan.toFixed(0)+' mm' : '-'}</td>
            <td style="font-size:12px">${p.kemiringan || '-'}</td>
            <td style="font-size:12px">${p.jenis_tanah || '-'}</td>
            <td style="font-size:12px">${p.penggunaan_lahan || '-'}</td>
            <td><span class="risiko-pill" style="${style}">${p.risiko_prediksi}</span></td>
            <td style="color:var(--accent-cyan);font-weight:600">${p.probabilitas ? (p.probabilitas*100).toFixed(1)+'%' : '-'}</td>
        </tr>`;
    }).join('');

    document.getElementById('tableInfo').textContent =
        `Menampilkan ${start+1} - ${Math.min(start+perPage, data.length)} dari ${data.length} titik`;

    renderPagination(data);
}

function renderPagination(data) {
    const total = Math.ceil(data.length / perPage);
    const container = document.getElementById('pageBtns');
    container.innerHTML = '';

    for (let i = 1; i <= Math.min(total, 10); i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'page-btn' + (i === page ? ' active' : '');
        btn.onclick = () => { page = i; renderTable(data); };
        container.appendChild(btn);
    }
}

document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    page = 1;
    const filtered = allData.filter(f => {
        const p = f.properties;
        return (p.risiko_prediksi||'').toLowerCase().includes(q)
            || (p.jenis_tanah||'').toLowerCase().includes(q)
            || (p.penggunaan_lahan||'').toLowerCase().includes(q)
            || (p.kemiringan||'').toLowerCase().includes(q);
    });
    renderTable(filtered);
});
</script>
@endpush
