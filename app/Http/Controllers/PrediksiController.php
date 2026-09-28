<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrediksiController extends Controller
{
    /**
     * URL Flask API Python
     * Di lokal: http://localhost:5000
     * Di VPS  : http://127.0.0.1:5000 (atau sesuai konfigurasi)
     */
    protected string $flaskUrl;

    public function __construct()
    {
        $this->flaskUrl = config('app.flask_api_url', 'http://localhost:5000');
    }

    /**
     * Tampilkan halaman form prediksi
     */
    public function index()
    {
        // Ambil info model dari Flask API
        $modelInfo = null;
        try {
            $resp = Http::timeout(3)->get("{$this->flaskUrl}/health");
            if ($resp->successful()) {
                $modelInfo = $resp->json();
            }
        } catch (\Exception $e) {
            // Flask belum jalan - tampil form tanpa info model
        }

        return view('admin.prediksi', compact('modelInfo'));
    }

    /**
     * Proses prediksi via Flask API
     */
    public function proses(Request $request)
    {
        // ── Validasi input ──────────────────────────────────────
        $validated = $request->validate([
            'curah_hujan'          => 'required|numeric|min:0',
            'slope'                => 'required|numeric|min:0',
            'jenis_tanah'          => 'required|string',
            'penggunaan_lahan'     => 'required|string',
            'kemiringan_grid_code' => 'required|integer|min:1|max:5',
            'jarak_drainase_m'     => 'required|numeric|min:0',
            'flood_hist_biner'     => 'required|integer|in:0,1',
            'longitude'            => 'required|numeric',
            'latitude'             => 'required|numeric',
            'model'                => 'nullable|in:rf,svm',
        ]);

        // ── Mapping nilai kategorik ke skor numerik ─────────────
        $tanah_map = [
            'Dystrudepts' => 1,
            'Hapludults'  => 2,
            'Endoaquepts' => 3,
        ];
        $lahan_risiko = [
            'Hutan'             => 1,
            'Semak Belukar'     => 2,
            'Hutan Mangrove'    => 2,
            'Lahan Pertanian'   => 3,
            'Lahan Terbuka'     => 4,
            'Tubuh Air'         => 5,
            'Kawasan Terbangun' => 5,
        ];

        $tanah_skor = $tanah_map[$validated['jenis_tanah']] ?? 2;
        $lahan_skor = $lahan_risiko[$validated['penggunaan_lahan']] ?? 3;

        // ── Kirim ke Flask API ──────────────────────────────────
        $payload = [
            'slope'                => (float) $validated['slope'],
            'curah_hujan'          => (float) $validated['curah_hujan'],
            'tanah_skor'           => (int)   $tanah_skor,
            'lahan_skor'           => (int)   $lahan_skor,
            'kemiringan_grid_code' => (int)   $validated['kemiringan_grid_code'],
            'jarak_drainase_m'     => (float) $validated['jarak_drainase_m'],
            'flood_hist_biner'     => (int)   $validated['flood_hist_biner'],
            'longitude'            => (float) $validated['longitude'],
            'latitude'             => (float) $validated['latitude'],
            'model'                => $validated['model'] ?? 'rf',
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->flaskUrl}/prediksi", $payload);

            if ($response->successful()) {
                $hasil = $response->json();
                $hasil['input'] = array_merge($validated, [
                    'tanah_skor' => $tanah_skor,
                    'lahan_skor' => $lahan_skor,
                ]);
            } else {
                Log::error('Flask API error', ['body' => $response->body()]);
                $hasil = null;
                $error = 'Flask API mengembalikan error: ' . $response->status();
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Flask API tidak bisa dihubungi', ['message' => $e->getMessage()]);
            $hasil = null;
            $error = 'Python API server belum aktif. Jalankan: python python-api/app.py';
        } catch (\Exception $e) {
            Log::error('Prediksi error', ['message' => $e->getMessage()]);
            $hasil = null;
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        return view('admin.prediksi', compact('hasil', 'error'));
    }

    /**
     * API endpoint JSON untuk AJAX (opsional)
     */
    public function apiPrediksi(Request $request)
    {
        $request->validate([
            'longitude' => 'required|numeric',
            'latitude'  => 'required|numeric',
        ]);

        // Nilai default berdasarkan koordinat (bisa dikembangkan dengan lookup tabel)
        $payload = [
            'slope'                => (float) $request->get('slope', 10),
            'curah_hujan'          => (float) $request->get('curah_hujan', 1991),
            'tanah_skor'           => (int)   $request->get('tanah_skor', 2),
            'lahan_skor'           => (int)   $request->get('lahan_skor', 3),
            'kemiringan_grid_code' => (int)   $request->get('kemiringan_grid_code', 2),
            'jarak_drainase_m'     => (float) $request->get('jarak_drainase_m', 500),
            'flood_hist_biner'     => (int)   $request->get('flood_hist_biner', 0),
            'longitude'            => (float) $request->longitude,
            'latitude'             => (float) $request->latitude,
            'model'                => $request->get('model', 'rf'),
        ];

        try {
            $response = Http::timeout(10)->post("{$this->flaskUrl}/prediksi", $payload);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Python API tidak tersedia: ' . $e->getMessage(),
            ], 503);
        }
    }
}
