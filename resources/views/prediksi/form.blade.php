@extends('layouts.app')
@section('title', 'Form Prediksi')

@push('styles')
<style>
    .pred-page { max-width: 900px; margin: 0 auto; padding: 40px 24px; }

    .pred-header { margin-bottom: 32px; }
    .pred-header h1 { font-family: "Outfit", sans-serif; font-size: 28px; font-weight: 800; margin-bottom: 4px; }
    .pred-header p { color: var(--text-secondary); font-size: 14px; }

    .pred-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }

    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px;
    }

    .form-title { font-size: 16px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

    .form-group { margin-bottom: 18px; }

    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 7px;
    }

    .form-control {
        width: 100%;
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 14px;
        color: var(--text-primary);
        font-size: 14px;
        font-family: "Inter", sans-serif;
        outline: none;
        transition: border-color 0.2s;
        -webkit-appearance: none;
        appearance: none;
    }

    .form-control:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }

    select.form-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2364748b' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }

    .form-hint { font-size: 11px; color: var(--text-muted); margin-top: 5px; }

    .btn-submit {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        font-family: "Inter", sans-serif;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(59,130,246,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 24px;
    }

    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(59,130,246,0.45); }
    .btn-submit:active { transform: translateY(0); }

    /* HASIL PREDIKSI */
    .result-card {
        border-radius: var(--radius);
        padding: 28px;
        border: 1px solid;
        animation: fadeInUp 0.5s ease;
        margin-bottom: 20px;
    }

    .result-label { font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; opacity: 0.7; }
    .result-kelas { font-family: "Outfit", sans-serif; font-size: 40px; font-weight: 800; margin-bottom: 4px; }
    .result-prob { font-size: 14px; opacity: 0.8; margin-bottom: 16px; }

    .prob-bar-big { height: 10px; background: rgba(255,255,255,0.1); border-radius: 5px; overflow: hidden; margin-bottom: 20px; }
    .prob-fill-big { height: 100%; border-radius: 5px; }

    .result-detail { font-size: 12px; opacity: 0.7; line-height: 2; }

    /* INFO CARDS */
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 20px; }

    .info-mini {
        background: rgba(255,255,255,0.04);
        border-radius: 10px;
        padding: 12px;
        font-size: 12px;
    }

    .info-mini-label { color: var(--text-muted); margin-bottom: 3px; }
    .info-mini-val { font-weight: 600; font-size: 13px; }

    /* MODEL INFO */
    .model-info {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }

    .model-info-title { font-size: 15px; font-weight: 700; margin-bottom: 16px; }

    .model-param {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 13px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .model-param:last-child { border: none; }
    .model-param span:first-child { color: var(--text-muted); }
    .model-param span:last-child { font-weight: 600; }

    @media (max-width: 768px) {
        .pred-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="pred-page">
    <div class="pred-header">
        <h1>🔮 Form Prediksi Risiko Banjir</h1>
        <p>Masukkan parameter wilayah untuk mendapatkan prediksi tingkat risiko banjir dari model ML</p>
    </div>

    <div class="pred-grid">

        <!-- FORM -->
        <div class="form-card">
            <div class="form-title">📋 Input Parameter</div>

            @if(session('error'))
                <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px;color:#ef4444">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('prediksi.proses') }}" id="predForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="curah_hujan">Curah Hujan Tahunan (mm/tahun)</label>
                    <input type="number" id="curah_hujan" name="curah_hujan" class="form-control"
                           placeholder="cth: 2500" value="{{ old('curah_hujan', isset($hasil) ? $hasil['input']['curah_hujan'] : '') }}"
                           min="0" max="10000" required>
                    <div class="form-hint">Rata-rata curah hujan tahunan di wilayah tersebut</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="kemiringan">Kelas Kemiringan Lereng</label>
                    <select id="kemiringan" name="kemiringan" class="form-control" required>
                        <option value="">-- Pilih Kemiringan --</option>
                        <option value="0-8% (Datar)" {{ old('kemiringan', isset($hasil) ? $hasil['input']['kemiringan'] : '') == '0-8% (Datar)' ? 'selected' : '' }}>0-8% (Datar)</option>
                        <option value="8-15% (Landai)" {{ old('kemiringan', isset($hasil) ? $hasil['input']['kemiringan'] : '') == '8-15% (Landai)' ? 'selected' : '' }}>8-15% (Landai)</option>
                        <option value="15-25% (Agak Curam)" {{ old('kemiringan', isset($hasil) ? $hasil['input']['kemiringan'] : '') == '15-25% (Agak Curam)' ? 'selected' : '' }}>15-25% (Agak Curam)</option>
                        <option value="25-45% (Curam)" {{ old('kemiringan', isset($hasil) ? $hasil['input']['kemiringan'] : '') == '25-45% (Curam)' ? 'selected' : '' }}>25-45% (Curam)</option>
                        <option value=">45% (Sangat Curam)" {{ old('kemiringan', isset($hasil) ? $hasil['input']['kemiringan'] : '') == '>45% (Sangat Curam)' ? 'selected' : '' }}>&gt;45% (Sangat Curam)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="jenis_tanah">Jenis Tanah</label>
                    <select id="jenis_tanah" name="jenis_tanah" class="form-control" required>
                        <option value="">-- Pilih Jenis Tanah --</option>
                        <option value="Dystrudepts">Dystrudepts</option>
                        <option value="Hapludults">Hapludults</option>
                        <option value="Hapludolls">Hapludolls</option>
                        <option value="Fluvaquents">Fluvaquents</option>
                        <option value="Dystrudepts/Hapludults">Dystrudepts/Hapludults</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="penggunaan_lahan">Penggunaan Lahan</label>
                    <select id="penggunaan_lahan" name="penggunaan_lahan" class="form-control" required>
                        <option value="">-- Pilih Penggunaan Lahan --</option>
                        <option value="Pemukiman">Pemukiman</option>
                        <option value="Pertanian Lahan Kering">Pertanian Lahan Kering</option>
                        <option value="Sawah">Sawah</option>
                        <option value="Hutan">Hutan</option>
                        <option value="Semak Belukar">Semak Belukar</option>
                        <option value="Tubuh Air">Tubuh Air</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="drainase">Skor Drainase (0-5)</label>
                    <input type="number" id="drainase" name="drainase" class="form-control"
                           placeholder="cth: 3" value="{{ old('drainase', isset($hasil) ? $hasil['input']['drainase'] : '') }}"
                           min="0" max="5" step="0.5" required>
                    <div class="form-hint">0 = drainase sangat baik, 5 = drainase sangat buruk</div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-brain"></i>
                    Prediksi Sekarang
                </button>
            </form>
        </div>

        <!-- HASIL & INFO -->
        <div>

            @if(isset($hasil))
            @php
                $kelas = $hasil['kelas'];
                $prob = $hasil['prob'];
                $warna = $hasil['warna'];
                $bgStyle = 'rgba(' . implode(',', sscanf($warna, '#%02x%02x%02x')) . ',0.08)';
                $borderStyle = 'rgba(' . implode(',', sscanf($warna, '#%02x%02x%02x')) . ',0.3)';
            @endphp
            <div class="result-card" style="background:{{ $bgStyle }};border-color:{{ $borderStyle }};color:{{ $warna }}">
                <div class="result-label">Hasil Prediksi</div>
                <div class="result-kelas">{{ strtoupper($kelas) }}</div>
                <div class="result-prob">Probabilitas: {{ round($prob * 100) }}%</div>
                <div class="prob-bar-big">
                    <div class="prob-fill-big" style="width:{{ $prob * 100 }}%;background:{{ $warna }}"></div>
                </div>
                <div class="result-detail">
                    @if($kelas === 'Tinggi')
                        ⚠️ Wilayah ini memiliki risiko banjir TINGGI. Diperlukan mitigasi dan sistem peringatan dini yang kuat.
                    @elseif($kelas === 'Sedang')
                        🟠 Wilayah ini memiliki risiko banjir SEDANG. Perlu pemantauan berkala dan persiapan evakuasi.
                    @elseif($kelas === 'Rendah')
                        🟡 Wilayah ini memiliki risiko banjir RENDAH. Tetap perhatikan kondisi curah hujan ekstrem.
                    @else
                        🟢 Wilayah ini memiliki risiko banjir SANGAT RENDAH. Kondisi relatif aman dari ancaman banjir.
                    @endif
                </div>
                <div class="info-grid" style="margin-top:16px">
                    <div class="info-mini" style="background:rgba(255,255,255,0.06)">
                        <div class="info-mini-label">Curah Hujan</div>
                        <div class="info-mini-val" style="color:{{ $warna }}">{{ $hasil['input']['curah_hujan'] }} mm</div>
                    </div>
                    <div class="info-mini" style="background:rgba(255,255,255,0.06)">
                        <div class="info-mini-label">Kemiringan</div>
                        <div class="info-mini-val" style="color:{{ $warna }}">{{ $hasil['input']['kemiringan'] }}</div>
                    </div>
                    <div class="info-mini" style="background:rgba(255,255,255,0.06)">
                        <div class="info-mini-label">Jenis Tanah</div>
                        <div class="info-mini-val" style="color:{{ $warna }}">{{ $hasil['input']['jenis_tanah'] }}</div>
                    </div>
                    <div class="info-mini" style="background:rgba(255,255,255,0.06)">
                        <div class="info-mini-label">Skor Drainase</div>
                        <div class="info-mini-val" style="color:{{ $warna }}">{{ $hasil['input']['drainase'] }}</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="model-info">
                <div class="model-info-title">🤖 Info Model ML</div>
                <div class="model-param"><span>Metode</span><span>Ensemble Stacking</span></div>
                <div class="model-param"><span>Base Learner 1</span><span>Random Forest</span></div>
                <div class="model-param"><span>Base Learner 2</span><span>XGBoost</span></div>
                <div class="model-param"><span>Meta Learner</span><span>Logistic Regression</span></div>
                <div class="model-param"><span>Akurasi</span><span style="color:var(--accent-green)">100%</span></div>
                <div class="model-param"><span>F1-Score</span><span style="color:var(--accent-green)">1.0000</span></div>
                <div class="model-param"><span>Data Training</span><span>651 titik</span></div>
                <div class="model-param"><span>Jumlah Fitur</span><span>12 parameter</span></div>
                <div class="model-param"><span>Kelas Output</span><span>4 kelas risiko</span></div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('predForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;
});
</script>
@endpush
