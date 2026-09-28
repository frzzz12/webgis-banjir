@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ── Stat Grid ─────────────────────────────────────── */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .stat-card {
        background: #fff; border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        padding: 20px 22px; border: 1px solid #f1f5f9;
        transition: transform .2s, box-shadow .2s;
        position: relative; overflow: hidden;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,.09); }
    .stat-card .accent-bar { position: absolute; bottom: 0; left: 0; right: 0; height: 3px; border-radius: 0 0 14px 14px; }
    .stat-card .stat-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; margin-bottom: 14px;
    }
    .stat-card .stat-val { font-size: 32px; font-weight: 800; line-height: 1; color: #1e293b; }
    .stat-card .stat-lbl { font-size: 12px; font-weight: 600; color: #64748b; margin-top: 4px; text-transform: uppercase; letter-spacing: .05em; }

    /* ── Section Title ──────────────────────────────────── */
    .section-title {
        font-size: 15px; font-weight: 700; color: #1e293b;
        margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
    }
    .section-title i { color: #1a73e8; }

    /* ── Card ───────────────────────────────────────────── */
    .card {
        background: #fff; border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 1px solid #f1f5f9; margin-bottom: 24px;
    }
    .card-header {
        padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .card-header h3 { font-size: 14px; font-weight: 700; color: #1e293b; }
    .card-body { padding: 20px; }

    /* ── Quick Actions ──────────────────────────────────── */
    .quick-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 28px; }
    .quick-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 9px;
        font-size: 13px; font-weight: 600;
        text-decoration: none; transition: all .15s;
        border: none; cursor: pointer;
    }
    .quick-btn-primary { background: #eff6ff; color: #1a73e8; border: 1px solid #bfdbfe; }
    .quick-btn-primary:hover { background: #dbeafe; }
    .quick-btn-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .quick-btn-success:hover { background: #dcfce7; }

    /* ── Tables ─────────────────────────────────────────── */
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; border-bottom: 1px solid #f1f5f9; }
    td { padding: 10px 14px; border-bottom: 1px solid #f8fafc; color: #374151; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f8fafc; }

    /* ── Risk Badge ─────────────────────────────────────── */
    .risk-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11.5px; font-weight: 700;
    }
    .risk-tinggi        { background: #fff1f2; color: #be123c; }
    .risk-sedang        { background: #fff7ed; color: #c2410c; }
    .risk-rendah        { background: #fefce8; color: #a16207; }
    .risk-sangat-rendah { background: #f0fdf4; color: #15803d; }

    /* ── Progress Bar ───────────────────────────────────── */
    .progress-bar-wrap { background: #f1f5f9; border-radius: 8px; height: 8px; overflow: hidden; margin-top: 6px; }
    .progress-bar-fill { height: 100%; border-radius: 8px; transition: width .6s ease; }

    /* ── Grid 2 col ─────────────────────────────────────── */
    .chart-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 600px) { .chart-row { grid-template-columns: 1fr; } .stat-grid { grid-template-columns: 1fr 1fr; } }

    /* ── Model Metrics ──────────────────────────────────── */
    .model-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px;
    }
    .metric-card {
        background: linear-gradient(135deg, #f8faff 0%, #eff6ff 100%);
        border: 1px solid #bfdbfe; border-radius: 12px;
        padding: 16px; text-align: center; position: relative; overflow: hidden;
        transition: transform .2s;
    }
    .metric-card:hover { transform: translateY(-2px); }
    .metric-card.rf { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #bbf7d0; }
    .metric-card.svm { background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); border-color: #e9d5ff; }
    .metric-card.best { border-color: #fbbf24; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); }
    .metric-val { font-size: 28px; font-weight: 800; line-height: 1; }
    .metric-val.rf  { color: #15803d; }
    .metric-val.svm { color: #7e22ce; }
    .metric-lbl { font-size: 11px; font-weight: 600; color: #64748b; margin-top: 5px; text-transform: uppercase; letter-spacing: .05em; }
    .metric-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10.5px; font-weight: 700; margin-top: 6px;
        padding: 2px 8px; border-radius: 20px;
    }
    .badge-rf  { background: #bbf7d0; color: #15803d; }
    .badge-svm { background: #e9d5ff; color: #7e22ce; }
    .badge-best { background: #fde68a; color: #92400e; }

    /* ── Kelurahan mini bar ─────────────────────────────── */
    .kel-bar { display: flex; height: 6px; border-radius: 4px; overflow: hidden; margin-top: 4px; }
    .kel-bar span { display: block; transition: width .5s ease; }
</style>
@endpush

@section('content')

{{-- ════════════════════════════════════════════════════
     STAT CARDS
═══════════════════════════════════════════════════════ --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;color:#1a73e8">
            <i class="fa-solid fa-location-dot"></i>
        </div>
        <div class="stat-val">{{ number_format($stats['total']) }}</div>
        <div class="stat-lbl">Total Titik Data</div>
        <div class="accent-bar" style="background:#1a73e8"></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff1f2;color:#ef4444">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-val" style="color:#ef4444">{{ number_format($stats['tinggi']) }}</div>
        <div class="stat-lbl">Risiko Tinggi</div>
        <div class="accent-bar" style="background:#ef4444"></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff7ed;color:#f97316">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="stat-val" style="color:#f97316">{{ number_format($stats['sedang']) }}</div>
        <div class="stat-lbl">Risiko Sedang</div>
        <div class="accent-bar" style="background:#f97316"></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fefce8;color:#eab308">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <div class="stat-val" style="color:#eab308">{{ number_format($stats['rendah']) }}</div>
        <div class="stat-lbl">Risiko Rendah</div>
        <div class="accent-bar" style="background:#eab308"></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;color:#22c55e">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-val" style="color:#22c55e">{{ number_format($stats['sangat_rendah']) }}</div>
        <div class="stat-lbl">Sangat Rendah</div>
        <div class="accent-bar" style="background:#22c55e"></div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════
     QUICK ACTIONS
═══════════════════════════════════════════════════════ --}}
<div class="section-title"><i class="fa-solid fa-bolt"></i> Aksi Cepat</div>
<div class="quick-actions">
    <a href="{{ route('admin.data-banjir.index') }}" class="quick-btn quick-btn-primary">
        <i class="fa-solid fa-table-list"></i> Kelola Data Banjir
    </a>
    <form method="POST" action="{{ route('admin.data-banjir.import') }}">
        @csrf
        <button type="submit" class="quick-btn quick-btn-success"
            onclick="return confirm('Import ulang data dari CSV? Data lama akan ditimpa.')">
            <i class="fa-solid fa-file-import"></i> Import Ulang CSV
        </button>
    </form>
    <a href="{{ url('/') }}" class="quick-btn" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;" target="_blank">
        <i class="fa-solid fa-map"></i> Lihat Peta Publik
    </a>
</div>

{{-- ════════════════════════════════════════════════════
     METRIK MODEL MACHINE LEARNING - SVM (RBF)
═══════════════════════════════════════════════════════ --}}
@if(isset($svmMetrics))
<div class="section-title"><i class="fa-solid fa-microchip"></i> Evaluasi Model Machine Learning — SVM (RBF)</div>
<div class="card" style="margin-bottom:28px;">
    <div class="card-header">
        <div>
            <h3 style="display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-chart-line" style="color:#7e22ce;"></i>
                Identifikasi Ancaman Bahaya Banjir – Support Vector Machine
            </h3>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                Model klasifikasi spasial tingkat risiko banjir Kecamatan Kendari Barat
            </div>
        </div>
        <span style="font-size:11.5px;font-weight:700;padding:4px 12px;background:#f3e8ff;color:#7e22ce;border-radius:20px;border:1px solid #d8b4fe;">
            <i class="fa-solid fa-shield-halved" style="font-size:10px;margin-right:3px;"></i>
            Algoritma: {{ $svmMetrics['model'] }} (Kernel {{ $svmMetrics['kernel'] }})
        </span>
    </div>
    <div class="card-body">
        {{-- Grid Kartu Metrik --}}
        <div class="model-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            {{-- Akurasi --}}
            <div class="metric-card svm">
                <div class="metric-val svm">{{ $svmMetrics['accuracy'] }}</div>
                <div class="metric-lbl">Akurasi</div>
                <div class="metric-badge badge-svm"><i class="fa-solid fa-bullseye" style="font-size:9px"></i> Overall Accuracy</div>
            </div>

            {{-- Precision --}}
            <div class="metric-card svm">
                <div class="metric-val svm">{{ $svmMetrics['precision'] }}</div>
                <div class="metric-lbl">Precision</div>
                <div class="metric-badge badge-svm"><i class="fa-solid fa-filter" style="font-size:9px"></i> Weighted</div>
            </div>

            {{-- Recall --}}
            <div class="metric-card svm">
                <div class="metric-val svm">{{ $svmMetrics['recall'] }}</div>
                <div class="metric-lbl">Recall</div>
                <div class="metric-badge badge-svm"><i class="fa-solid fa-bullseye" style="font-size:9px"></i> Weighted</div>
            </div>

            {{-- F1-Score --}}
            <div class="metric-card svm">
                <div class="metric-val svm">{{ $svmMetrics['f1_score'] }}</div>
                <div class="metric-lbl">F1-Score</div>
                <div class="metric-badge badge-svm"><i class="fa-solid fa-scale-balanced" style="font-size:9px"></i> Weighted</div>
            </div>

            {{-- AUC-ROC --}}
            <div class="metric-card svm">
                <div class="metric-val svm">{{ $svmMetrics['auc_roc'] }}</div>
                <div class="metric-lbl">AUC-ROC</div>
                <div class="metric-badge badge-svm"><i class="fa-solid fa-chart-area" style="font-size:9px"></i> Macro Avg</div>
            </div>

            {{-- CV Accuracy --}}
            <div class="metric-card svm">
                <div class="metric-val svm">{{ $svmMetrics['cv_mean'] }}</div>
                <div class="metric-lbl">CV Accuracy</div>
                <div class="metric-badge badge-svm"><i class="fa-solid fa-arrows-rotate" style="font-size:9px"></i> Mean (Std: {{ $svmMetrics['cv_std'] }})</div>
            </div>
        </div>

        {{-- Tabel Rincian Evaluasi --}}
        <div style="margin-top:20px; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:12.5px; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0;">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1.5px solid #e2e8f0; text-align:left;">
                        <th style="padding:10px 14px; font-weight:700; color:#334155; width:220px;">Metrik Evaluasi</th>
                        <th style="padding:10px 14px; font-weight:700; color:#7e22ce; width:160px;">Hasil Nilai SVM (RBF)</th>
                        <th style="padding:10px 14px; font-weight:700; color:#334155;">Interpretasi &amp; Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:10px 14px; font-weight:700; color:#1e293b;">Akurasi (Accuracy)</td>
                        <td style="padding:10px 14px; font-weight:800; color:#7e22ce; font-size:14px;">96.18%</td>
                        <td style="padding:10px 14px; color:#64748b;">Tingkat ketepatan klasifikasi total titik risiko banjir pada data pengujian.</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:10px 14px; font-weight:700; color:#1e293b;">Precision (Weighted)</td>
                        <td style="padding:10px 14px; font-weight:800; color:#7e22ce; font-size:14px;">97.48%</td>
                        <td style="padding:10px 14px; color:#64748b;">Akurasi prediksi positif, meminimalisir kesalahan alarm banjir palsu (*false positive*).</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:10px 14px; font-weight:700; color:#1e293b;">Recall (Weighted)</td>
                        <td style="padding:10px 14px; font-weight:800; color:#7e22ce; font-size:14px;">96.18%</td>
                        <td style="padding:10px 14px; color:#64748b;">Kemampuan mendeteksi seluruh area yang memang rawan banjir secara nyata (*true positive*).</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:10px 14px; font-weight:700; color:#1e293b;">F1-Score (Weighted)</td>
                        <td style="padding:10px 14px; font-weight:800; color:#7e22ce; font-size:14px;">96.53%</td>
                        <td style="padding:10px 14px; color:#64748b;">Harmonic mean antara presisi dan recall, membuktikan model sangat seimbang dan handal.</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:10px 14px; font-weight:700; color:#1e293b;">AUC-ROC (Macro)</td>
                        <td style="padding:10px 14px; font-weight:800; color:#7e22ce; font-size:14px;">0.9978</td>
                        <td style="padding:10px 14px; color:#64748b;">Daya pembeda (*discriminative power*) model terhadap antar-tingkat risiko mendekati nilai sempurna 1.0.</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 14px; font-weight:700; color:#1e293b;">K-Fold Cross Validation</td>
                        <td style="padding:10px 14px; font-weight:800; color:#7e22ce; font-size:14px;">97.18% <span style="font-size:11px; color:#9333ea; font-weight:600;">(±0.45%)</span></td>
                        <td style="padding:10px 14px; color:#64748b;">Rata-rata akurasi pada pengujian silang berulang dengan deviasi standar sangat kecil (&plusmn;0.45%), membuktikan model stabil tanpa *overfitting*.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Keterangan fitur --}}
        <div style="margin-top:16px;padding:12px 16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
            <div style="font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;">
                <i class="fa-solid fa-list-check" style="color:#7e22ce;margin-right:5px"></i>
                Parameter Fitur Input yang Digunakan ({{ count($svmMetrics['features'] ?? []) }} Parameter Spasial)
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach($svmMetrics['features'] ?? [] as $feat)
                <span style="font-size:11px;font-weight:600;padding:3px 10px;background:#f3e8ff;color:#7e22ce;border-radius:20px;border:1px solid #e9d5ff;">
                    {{ $feat }}
                </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════
     DISTRIBUSI RISIKO + DATA TERBARU
═══════════════════════════════════════════════════════ --}}
<div class="chart-row">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-chart-pie" style="color:#1a73e8;margin-right:7px"></i> Distribusi Risiko Banjir</h3>
        </div>
        <div class="card-body">
            @php
                $total = $stats['total'] ?: 1;
                $dist = [
                    ['label' => 'Sangat Rendah', 'val' => $stats['sangat_rendah'], 'color' => '#22c55e', 'class' => 'risk-sangat-rendah'],
                    ['label' => 'Rendah',         'val' => $stats['rendah'],        'color' => '#eab308', 'class' => 'risk-rendah'],
                    ['label' => 'Sedang',          'val' => $stats['sedang'],        'color' => '#f97316', 'class' => 'risk-sedang'],
                    ['label' => 'Tinggi',          'val' => $stats['tinggi'],        'color' => '#ef4444', 'class' => 'risk-tinggi'],
                ];
            @endphp
            @foreach($dist as $d)
            @php $pct = round($d['val'] / $total * 100); @endphp
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="risk-badge {{ $d['class'] }}">{{ $d['label'] }}</span>
                    <span style="font-size:12px;font-weight:700;color:#374151">{{ $d['val'] }} <span style="color:#94a3b8">({{ $pct }}%)</span></span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width:{{ $pct }}%;background:{{ $d['color'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-clock-rotate-left" style="color:#1a73e8;margin-right:7px"></i> 10 Data Terbaru</h3>
            <a href="{{ route('admin.data-banjir.index') }}" style="font-size:12px;color:#1a73e8;text-decoration:none;font-weight:600">Lihat semua →</a>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Koordinat</th>
                        <th>Kelurahan</th>
                        <th>Label</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($terbaru as $row)
                    @php
                        $labelClass = match($row->label) {
                            'tinggi'        => 'risk-tinggi',
                            'sedang'        => 'risk-sedang',
                            'rendah'        => 'risk-rendah',
                            'sangat rendah' => 'risk-sangat-rendah',
                            default         => '',
                        };
                    @endphp
                    <tr>
                        <td style="font-size:11.5px;font-family:monospace">
                            {{ number_format($row->latitude, 4) }},<br>
                            {{ number_format($row->longitude, 4) }}
                        </td>
                        <td style="font-size:12px">{{ $row->kelurahan ?? '-' }}</td>
                        <td><span class="risk-badge {{ $labelClass }}">{{ ucfirst($row->label ?? '-') }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════
     RISIKO PER KELURAHAN
═══════════════════════════════════════════════════════ --}}
<div class="section-title" style="margin-top:4px"><i class="fa-solid fa-map-location-dot"></i> Risiko Banjir Per Kelurahan</div>
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-city" style="color:#1a73e8;margin-right:7px"></i> Rekapitulasi Risiko per Kelurahan - Kec. Kendari Barat</h3>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Kelurahan</th>
                    <th>Risiko Dominan</th>
                    <th>Total Titik</th>
                    <th style="color:#ef4444">Tinggi</th>
                    <th style="color:#f97316">Sedang</th>
                    <th style="color:#eab308">Rendah</th>
                    <th style="color:#22c55e">Sangat Rendah</th>
                    <th>Distribusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perKelurahan as $k)
                @php
                    $total_k = max($k->total, 1);
                    $dominanClass = match($k->risiko_dominan) {
                        'tinggi'        => 'risk-tinggi',
                        'sedang'        => 'risk-sedang',
                        'rendah'        => 'risk-rendah',
                        'sangat rendah' => 'risk-sangat-rendah',
                        default         => '',
                    };
                    $pT = round($k->tinggi        / $total_k * 100);
                    $pS = round($k->sedang         / $total_k * 100);
                    $pR = round($k->rendah         / $total_k * 100);
                    $pSR= round($k->sangat_rendah  / $total_k * 100);
                @endphp
                <tr>
                    <td style="font-weight:600">{{ $k->kelurahan }}</td>
                    <td>
                        <span class="risk-badge {{ $dominanClass }}">
                            {{ ucfirst($k->risiko_dominan) }}
                        </span>
                    </td>
                    <td style="font-weight:700;color:#1e293b">{{ $k->total }}</td>
                    <td style="font-weight:600;color:#ef4444">{{ $k->tinggi }}</td>
                    <td style="font-weight:600;color:#f97316">{{ $k->sedang }}</td>
                    <td style="font-weight:600;color:#eab308">{{ $k->rendah }}</td>
                    <td style="font-weight:600;color:#22c55e">{{ $k->sangat_rendah }}</td>
                    <td style="min-width:100px">
                        <div class="kel-bar">
                            <span style="width:{{ $pT }}%;background:#ef4444"></span>
                            <span style="width:{{ $pS }}%;background:#f97316"></span>
                            <span style="width:{{ $pR }}%;background:#eab308"></span>
                            <span style="width:{{ $pSR }}%;background:#22c55e"></span>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;margin-top:2px">
                            {{ $pT }}% T · {{ $pS }}% S · {{ $pR }}% R · {{ $pSR }}% SR
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
