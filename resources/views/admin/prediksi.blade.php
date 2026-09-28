@extends('layouts.admin')
@section('title', 'Prediksi Potensi Banjir')
@section('page-title', 'Prediksi Potensi Banjir')

@section('content')
<div style="max-width:760px;">

    {{-- ── Info Model ─────────────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,#faf5ff,#f3e8ff);border:1.5px solid #d8b4fe;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
        <div style="width:42px;height:42px;border-radius:10px;background:#7e22ce;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fa-solid fa-microchip" style="color:white;font-size:18px"></i>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#6b21a8;text-transform:uppercase;letter-spacing:.05em">Model Klasifikasi Terpilih</div>
            <div style="font-size:15px;font-weight:800;color:#581c87">Support Vector Machine (SVM - RBF)</div>
            <div style="font-size:11.5px;color:#7e22ce;margin-top:2px">
                Akurasi: <b>96.18%</b> &nbsp;|&nbsp;
                Precision: <b>97.48%</b> &nbsp;|&nbsp;
                Recall: <b>96.18%</b> &nbsp;|&nbsp;
                F1-Score: <b>96.53%</b> &nbsp;|&nbsp;
                AUC-ROC: <b>0.9978</b>
            </div>
        </div>
        <div style="margin-left:auto;font-size:11px;color:#7e22ce;background:#e9d5ff;border-radius:20px;padding:4px 12px;font-weight:700">
            <i class="fa-solid fa-shield-halved" style="font-size:10px;margin-right:4px;"></i>SVM RBF
        </div>
    </div>

    {{-- ── Error ─────────────────────────────────────────────── --}}
    @if(isset($error))
    <div style="background:#fdecea;border:1.5px solid #ef9a9a;border-radius:12px;padding:12px 18px;margin-bottom:20px;font-size:13px;color:#c62828;">
        <i class="fa-solid fa-circle-xmark" style="margin-right:8px"></i>{{ $error }}
    </div>
    @endif

    {{-- ── Hasil Prediksi ──────────────────────────────────────── --}}
    @if(isset($hasil) && $hasil['status'] === 'success')
    @php
        $warna = $hasil['warna'];
        $label = $hasil['prediksi_display'];
        $prob  = $hasil['probabilitas_utama'];
        $icon  = $hasil['icon'];
        $proba = $hasil['probabilitas'];
    @endphp
    <div style="background:{{ $warna }}18;border:2px solid {{ $warna }}50;border-radius:14px;padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
            <div style="width:54px;height:54px;border-radius:14px;background:{{ $warna }};display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px {{ $warna }}50">
                <i class="fa-solid {{ $icon }}" style="color:white;font-size:22px"></i>
            </div>
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.06em">Hasil Prediksi Model</div>
                <div style="font-size:24px;font-weight:900;color:{{ $warna }};letter-spacing:-.02em">Risiko {{ $label }}</div>
                <div style="font-size:12.5px;color:#64748b;margin-top:2px">
                    Probabilitas: <b style="color:#1e293b">{{ $prob }}%</b>
                    &nbsp;|&nbsp; Model: <b>{{ $hasil['model_digunakan'] }}</b>
                </div>
            </div>
        </div>

        {{-- Bar probabilitas semua kelas --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            @foreach([
                ['sangat_rendah', 'Sangat Rendah', '#27ae60'],
                ['rendah',        'Rendah',        '#f1c40f'],
                ['sedang',        'Sedang',        '#e67e22'],
                ['tinggi',        'Tinggi',        '#e74c3c'],
            ] as [$key, $lbl, $clr])
            <div style="background:white;border-radius:9px;padding:9px 12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <span style="font-size:11px;font-weight:700;color:#1e293b">{{ $lbl }}</span>
                    <span style="font-size:12px;font-weight:800;color:{{ $clr }}">{{ number_format($proba[$key]*100,1) }}%</span>
                </div>
                <div style="height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden">
                    <div style="height:100%;width:{{ number_format($proba[$key]*100,1) }}%;background:{{ $clr }};border-radius:99px;transition:width .6s ease"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Form Input ─────────────────────────────────────────── --}}
    <div style="background:#fff;border-radius:14px;padding:28px;border:1px solid #f1f5f9;box-shadow:0 2px 12px rgba(0,0,0,.06)">
        <h2 style="font-size:15px;font-weight:700;color:#1e293b;margin-bottom:4px">
            <i class="fa-solid fa-sliders" style="color:#1a73e8;margin-right:8px"></i>Parameter Input Prediksi
        </h2>
        <p style="font-size:12.5px;color:#64748b;margin-bottom:22px">Masukkan parameter lokasi untuk memprediksi tingkat potensi banjir.</p>

        <form method="POST" action="{{ route('admin.prediksi.proses') }}" id="form-prediksi">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

                {{-- Koordinat --}}
                <div>
                    <label class="lbl">Longitude</label>
                    <input type="number" name="longitude" step="0.000001" value="{{ old('longitude', 122.523) }}"
                           class="inp" placeholder="Contoh: 122.523" required>
                </div>
                <div>
                    <label class="lbl">Latitude</label>
                    <input type="number" name="latitude" step="0.000001" value="{{ old('latitude', -3.937) }}"
                           class="inp" placeholder="Contoh: -3.937" required>
                </div>

                {{-- Curah Hujan --}}
                <div>
                    <label class="lbl">Curah Hujan (mm/tahun)</label>
                    <select name="curah_hujan" class="inp" required>
                        <option value="">Pilih kelas curah hujan</option>
                        <option value="1776" {{ old('curah_hujan')=='1776'?'selected':'' }}>1731 - 1821 mm (sangat rendah)</option>
                        <option value="1852" {{ old('curah_hujan')=='1852'?'selected':'' }}>1821 - 1884 mm (rendah)</option>
                        <option value="1918" {{ old('curah_hujan','1918')=='1918'?'selected':'' }}>1884 - 1953 mm (sedang)</option>
                        <option value="1991" {{ old('curah_hujan')=='1991'?'selected':'' }}>1953 - 2030 mm (tinggi)</option>
                        <option value="2074" {{ old('curah_hujan')=='2074'?'selected':'' }}>2030 - 2118 mm (sangat tinggi)</option>
                    </select>
                </div>

                {{-- Slope --}}
                <div>
                    <label class="lbl">Nilai Slope / Kemiringan (°)</label>
                    <input type="number" name="slope" step="0.01" value="{{ old('slope', 10) }}"
                           class="inp" placeholder="Contoh: 10.5" required min="0">
                </div>

                {{-- Kemiringan Grid Code --}}
                <div>
                    <label class="lbl">Kelas Kemiringan Lereng</label>
                    <select name="kemiringan_grid_code" class="inp" required>
                        <option value="">Pilih kelas kemiringan</option>
                        <option value="1" {{ old('kemiringan_grid_code')=='1'?'selected':'' }}>1 - 0-8% (Datar)</option>
                        <option value="2" {{ old('kemiringan_grid_code','2')=='2'?'selected':'' }}>2 - 8-15% (Landai)</option>
                        <option value="3" {{ old('kemiringan_grid_code')=='3'?'selected':'' }}>3 - 15-25% (Agak Curam)</option>
                        <option value="4" {{ old('kemiringan_grid_code')=='4'?'selected':'' }}>4 - 25-45% (Curam)</option>
                        <option value="5" {{ old('kemiringan_grid_code')=='5'?'selected':'' }}>5 - >45% (Sangat Curam)</option>
                    </select>
                </div>

                {{-- Jenis Tanah --}}
                <div>
                    <label class="lbl">Jenis Tanah</label>
                    <select name="jenis_tanah" class="inp" required>
                        <option value="">Pilih jenis tanah</option>
                        <option value="Dystrudepts"  {{ old('jenis_tanah')=='Dystrudepts'?'selected':'' }}>Dystrudepts (Skor 1 - drainase baik)</option>
                        <option value="Hapludults"   {{ old('jenis_tanah')=='Hapludults'?'selected':'' }}>Hapludults (Skor 2 - drainase sedang)</option>
                        <option value="Endoaquepts"  {{ old('jenis_tanah')=='Endoaquepts'?'selected':'' }}>Endoaquepts (Skor 3 - drainase buruk)</option>
                    </select>
                </div>

                {{-- Penggunaan Lahan --}}
                <div>
                    <label class="lbl">Penggunaan Lahan</label>
                    <select name="penggunaan_lahan" class="inp" required>
                        <option value="">Pilih penggunaan lahan</option>
                        <option value="Hutan"             {{ old('penggunaan_lahan')=='Hutan'?'selected':'' }}>Hutan (Skor 1)</option>
                        <option value="Semak Belukar"      {{ old('penggunaan_lahan')=='Semak Belukar'?'selected':'' }}>Semak Belukar (Skor 2)</option>
                        <option value="Hutan Mangrove"     {{ old('penggunaan_lahan')=='Hutan Mangrove'?'selected':'' }}>Hutan Mangrove (Skor 2)</option>
                        <option value="Lahan Pertanian"    {{ old('penggunaan_lahan')=='Lahan Pertanian'?'selected':'' }}>Lahan Pertanian (Skor 3)</option>
                        <option value="Lahan Terbuka"      {{ old('penggunaan_lahan')=='Lahan Terbuka'?'selected':'' }}>Lahan Terbuka (Skor 4)</option>
                        <option value="Tubuh Air"           {{ old('penggunaan_lahan')=='Tubuh Air'?'selected':'' }}>Tubuh Air (Skor 5)</option>
                        <option value="Kawasan Terbangun"   {{ old('penggunaan_lahan')=='Kawasan Terbangun'?'selected':'' }}>Kawasan Terbangun (Skor 5)</option>
                    </select>
                </div>

                {{-- Jarak Drainase --}}
                <div>
                    <label class="lbl">Jarak ke Saluran Drainase Terdekat (meter)</label>
                    <input type="number" name="jarak_drainase_m" step="0.1" value="{{ old('jarak_drainase_m', 500) }}"
                           class="inp" placeholder="Contoh: 350" required min="0">
                </div>

                {{-- Riwayat Banjir --}}
                <div>
                    <label class="lbl">Riwayat Kejadian Banjir</label>
                    <select name="flood_hist_biner" class="inp" required>
                        <option value="">Pilih riwayat banjir</option>
                        <option value="0" {{ old('flood_hist_biner')=='0'?'selected':'' }}>0 - Tidak pernah banjir</option>
                        <option value="1" {{ old('flood_hist_biner')=='1'?'selected':'' }}>1 - Pernah banjir</option>
                    </select>
                </div>

                {{-- Pilih Model: SVM (RBF) --}}
                <div>
                    <label class="lbl">Algoritma Machine Learning</label>
                    <select name="model" class="inp">
                        <option value="svm" selected>Support Vector Machine (SVM - RBF) — Akurasi 96.18%</option>
                    </select>
                </div>

            </div>

            <button type="submit" id="btn-submit"
                    style="margin-top:22px;width:100%;padding:13px;background:linear-gradient(135deg,#0d47a1,#1a73e8);color:white;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px">
                <i class="fa-solid fa-magnifying-glass-chart"></i>
                <span>Analisis Prediksi</span>
            </button>
        </form>
    </div>
</div>

<style>
.lbl {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #43474f;
    display: block;
    margin-bottom: 6px;
}
.inp {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    font-size: 13px;
    outline: none;
    transition: border-color .15s;
    box-sizing: border-box;
    background: white;
}
.inp:focus { border-color: #1a73e8; }
</style>

<script>
document.getElementById('form-prediksi').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Memproses...</span>';
    btn.disabled = true;
    btn.style.opacity = '0.8';
});
</script>
@endsection
