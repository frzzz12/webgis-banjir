<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'        => DB::table('titik_banjir')->count(),
            'tinggi'       => DB::table('titik_banjir')->where('label', 'tinggi')->count(),
            'sedang'       => DB::table('titik_banjir')->where('label', 'sedang')->count(),
            'rendah'       => DB::table('titik_banjir')->where('label', 'rendah')->count(),
            'sangat_rendah'=> DB::table('titik_banjir')->where('label', 'sangat rendah')->count(),
        ];

        $terbaru = DB::table('titik_banjir')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ── Statistik per kelurahan ──────────────────────────────
        $perKelurahan = DB::table('titik_banjir')
            ->whereNotNull('kelurahan')
            ->selectRaw("
                kelurahan,
                COUNT(*) as total,
                SUM(CASE WHEN label = 'tinggi'        THEN 1 ELSE 0 END) as tinggi,
                SUM(CASE WHEN label = 'sedang'        THEN 1 ELSE 0 END) as sedang,
                SUM(CASE WHEN label = 'rendah'        THEN 1 ELSE 0 END) as rendah,
                SUM(CASE WHEN label = 'sangat rendah' THEN 1 ELSE 0 END) as sangat_rendah
            ")
            ->groupBy('kelurahan')
            ->orderBy('kelurahan')
            ->get()
            ->map(function ($r) {
                // Risiko dominan
                $counts = [
                    'tinggi'        => (int) $r->tinggi,
                    'sedang'        => (int) $r->sedang,
                    'rendah'        => (int) $r->rendah,
                    'sangat rendah' => (int) $r->sangat_rendah,
                ];
                arsort($counts);
                $r->risiko_dominan = array_key_first($counts);
                return $r;
            });

        // ── Metrik model Machine Learning: SVM (RBF) ─────────────
        $svmMetrics = [
            'model'     => 'Support Vector Machine (SVM)',
            'kernel'    => 'Radial Basis Function (RBF)',
            'accuracy'  => '96.18%',
            'precision' => '97.48%',
            'recall'    => '96.18%',
            'f1_score'  => '96.53%',
            'auc_roc'   => '0.9978',
            'cv_mean'   => '97.18%',
            'cv_std'    => '±0.45%',
            'features'  => [
                'Kemiringan Lereng (Slope)',
                'Curah Hujan',
                'Jenis Tanah',
                'Penggunaan Lahan',
                'Kemiringan Grid Code',
                'Jarak Saluran Drainase (m)',
                'Riwayat Kejadian Banjir',
                'Longitude',
                'Latitude',
            ],
        ];

        return view('admin.dashboard', compact('stats', 'terbaru', 'perKelurahan', 'svmMetrics'));
    }
}
