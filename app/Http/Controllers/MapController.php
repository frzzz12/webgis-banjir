<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function index()
    {
        return view('map.index');
    }

    /**
     * GeoJSON batas kecamatan Kendari Barat
     */
    public function geojsonBatas()
    {
        $path = public_path('geojson/batas_kendari_barat.geojson');
        if (!file_exists($path)) {
            return response()->json(['error' => 'File GeoJSON tidak ditemukan'], 404);
        }
        return response()->file($path, ['Content-Type' => 'application/json']);
    }

    /**
     * GeoJSON titik-titik lokasi banjir
     */
    public function geojsonTitikBanjir()
    {
        try {
            $titik    = DB::table('titik_banjir')->get();
            $features = $titik->map(function ($row) {
                return [
                    'type'     => 'Feature',
                    'geometry' => [
                        'type'        => 'Point',
                        'coordinates' => [(float) $row->longitude, (float) $row->latitude],
                    ],
                    'properties' => [
                        'id'               => $row->id,
                        'curah_hujan'      => $row->curah_hujan,
                        'jenis_tanah'      => $row->jenis_tanah,
                        'kemiringan'       => $row->kemiringan,
                        'penggunaan_lahan' => $row->penggunaan_lahan,
                        'drainase_skor'    => $row->drainase_skor,
                        'flood_hist'       => $row->flood_hist,
                        'slope'            => $row->slope   ?? null,
                        'geology'          => $row->geology ?? null,
                        'label'            => $row->label,
                        'kelurahan'        => $row->kelurahan ?? null,
                    ],
                ];
            });

            return response()->json([
                'type'     => 'FeatureCollection',
                'features' => $features,
            ]);

        } catch (\Exception $e) {
            return $this->geojsonFromCsv();
        }
    }

    /**
     * GeoJSON kelurahan + statistik risiko banjir per kelurahan
     * Termasuk prediksi ML untuk kelurahan tanpa data
     */
    public function geojsonKelurahan()
    {
        $geojsonPath = public_path('geojson/batas_kelurahan_real.geojson');
        if (!file_exists($geojsonPath)) {
            $geojsonPath = public_path('geojson/batas_kelurahan.geojson');
        }
        if (!file_exists($geojsonPath)) {
            return response()->json(['error' => 'File GeoJSON kelurahan tidak ditemukan'], 404);
        }

        $geojson = json_decode(file_get_contents($geojsonPath), true);

        // Statistik risiko per kelurahan dari database
        $stats = DB::table('titik_banjir')
            ->whereNotNull('kelurahan')
            ->selectRaw('
                kelurahan,
                COUNT(*) as total,
                SUM(CASE WHEN label = \'tinggi\'        THEN 1 ELSE 0 END) as tinggi,
                SUM(CASE WHEN label = \'sedang\'        THEN 1 ELSE 0 END) as sedang,
                SUM(CASE WHEN label = \'rendah\'        THEN 1 ELSE 0 END) as rendah,
                SUM(CASE WHEN label = \'sangat rendah\' THEN 1 ELSE 0 END) as sangat_rendah
            ')
            ->groupBy('kelurahan')
            ->get()
            ->keyBy('kelurahan');

        $riskOrder  = ['tinggi' => 4, 'sedang' => 3, 'rendah' => 2, 'sangat rendah' => 1];
        $riskColors = [
            'tinggi'        => '#ef4444',
            'sedang'        => '#f97316',
            'rendah'        => '#eab308',
            'sangat rendah' => '#22c55e',
            'prediksi'      => '#8b5cf6',  // ungu untuk prediksi ML
        ];

        foreach ($geojson['features'] as &$ft) {
            $nama = $ft['properties']['kelurahan'];

            if (isset($stats[$nama])) {
                $s = $stats[$nama];

                // Dominan risiko = label dengan jumlah terbanyak
                $labelCounts = [
                    'tinggi'        => (int) $s->tinggi,
                    'sedang'        => (int) $s->sedang,
                    'rendah'        => (int) $s->rendah,
                    'sangat rendah' => (int) $s->sangat_rendah,
                ];
                arsort($labelCounts);
                $dominan   = array_key_first($labelCounts);
                $pctTinggi = $s->total > 0 ? round($s->tinggi / $s->total * 100) : 0;

                $ft['properties']['sumber']        = 'data';
                $ft['properties']['risiko_dominan'] = $dominan;
                $ft['properties']['warna']          = $riskColors[$dominan] ?? '#94a3b8';
                $ft['properties']['total_titik']    = (int) $s->total;
                $ft['properties']['tinggi']         = (int) $s->tinggi;
                $ft['properties']['sedang']         = (int) $s->sedang;
                $ft['properties']['rendah']         = (int) $s->rendah;
                $ft['properties']['sangat_rendah']  = (int) $s->sangat_rendah;
                $ft['properties']['pct_tinggi']     = $pctTinggi;
            } else {
                // Tidak ada data → prediksi dari ML via centroid
                $prediksi = $this->prediksiCentroid($ft['geometry']['coordinates'][0]);

                $ft['properties']['sumber']        = 'prediksi_ml';
                $ft['properties']['risiko_dominan'] = $prediksi['label'];
                $ft['properties']['warna']          = $riskColors['prediksi'];
                $ft['properties']['total_titik']    = 0;
                $ft['properties']['tinggi']         = 0;
                $ft['properties']['sedang']         = 0;
                $ft['properties']['rendah']         = 0;
                $ft['properties']['sangat_rendah']  = 0;
                $ft['properties']['pct_tinggi']     = 0;
                $ft['properties']['prediksi_label'] = $prediksi['label'];
                $ft['properties']['prediksi_pct']   = $prediksi['probabilitas'];
            }
        }

        return response()->json($geojson)->header('Content-Type', 'application/json');
    }

    /**
     * Prediksi risiko menggunakan centroid polygon (rata-rata koordinat)
     * dengan memanggil Flask ML API
     */
    private function prediksiCentroid(array $ring): array
    {
        // Hitung centroid
        $sumLon = $sumLat = 0;
        $n = count($ring);
        foreach ($ring as $pt) {
            $sumLon += $pt[0];
            $sumLat += $pt[1];
        }
        $lon = $sumLon / $n;
        $lat = $sumLat / $n;

        // Ambil rata-rata parameter dari data terdekat di DB
        $nearby = DB::table('titik_banjir')
            ->selectRaw('AVG(slope) as slope, AVG(drainase_skor) as drainase')
            ->whereNotNull('slope')
            ->first();

        $payload = [
            'slope'                => $nearby->slope    ?? 5.0,
            'curah_hujan'          => 1900,
            'tanah_skor'           => 2,
            'lahan_skor'           => 4,
            'kemiringan_grid_code' => 2,
            'jarak_drainase_m'     => $nearby->drainase ?? 300,
            'flood_hist_biner'     => 0,
            'longitude'            => $lon,
            'latitude'             => $lat,
            'model'                => 'rf',
        ];

        $apiUrl = env('PYTHON_API_URL');
        if (empty($apiUrl)) {
            return ['label' => 'rendah', 'probabilitas' => 0];
        }

        try {
            $client  = new \GuzzleHttp\Client(['timeout' => 3]);
            $resp    = $client->post("{$apiUrl}/prediksi", ['json' => $payload]);
            $result  = json_decode($resp->getBody(), true);
            return [
                'label'        => $result['prediksi_label']   ?? 'rendah',
                'probabilitas' => $result['probabilitas_utama'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['label' => 'rendah', 'probabilitas' => 0];
        }
    }

    /**
     * GeoJSON titik-titik untuk komputasi Voronoi Zona Risiko per Kelurahan
     * Mengembalikan semua titik dengan label, kelurahan, dan parameter ML
     * Data ini dipakai oleh Turf.js di frontend untuk membuat sub-segmentasi
     */
    public function geojsonZonaRisiko()
    {
        try {
            $titik = DB::table('titik_banjir')
                ->whereNotNull('label')
                ->select([
                    'id', 'longitude', 'latitude', 'label', 'kelurahan',
                    'slope', 'curah_hujan', 'jenis_tanah', 'kemiringan',
                    'penggunaan_lahan', 'drainase_skor', 'flood_hist',
                ])
                ->get();

            $riskColors = [
                'tinggi'        => '#ef4444',
                'sedang'        => '#f97316',
                'rendah'        => '#eab308',
                'sangat rendah' => '#22c55e',
            ];

            $features = $titik->map(function ($row) use ($riskColors) {
                $label = strtolower(trim($row->label ?? ''));
                return [
                    'type'     => 'Feature',
                    'geometry' => [
                        'type'        => 'Point',
                        'coordinates' => [(float) $row->longitude, (float) $row->latitude],
                    ],
                    'properties' => [
                        'id'               => $row->id,
                        'label'            => $label,
                        'kelurahan'        => $row->kelurahan,
                        'warna'            => $riskColors[$label] ?? '#94a3b8',
                        'slope'            => $row->slope,
                        'curah_hujan'      => $row->curah_hujan,
                        'jenis_tanah'      => $row->jenis_tanah,
                        'kemiringan'       => $row->kemiringan,
                        'penggunaan_lahan' => $row->penggunaan_lahan,
                        'drainase_skor'    => $row->drainase_skor,
                        'flood_hist'       => $row->flood_hist,
                    ],
                ];
            });

            return response()->json([
                'type'     => 'FeatureCollection',
                'features' => $features,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fallback: baca CSV Data Kejadian Banjir langsung
     */
    private function geojsonFromCsv()
    {
        $csvPath = base_path('CSV PARAMETER BANJIR/Data Kejadian Banjir.csv');
        if (!file_exists($csvPath)) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }

        $features = [];
        $handle   = fopen($csvPath, 'r');
        $headers  = fgetcsv($handle);
        $headers  = array_map('trim', $headers);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            $lon  = isset($data['longitude']) ? (float) $data['longitude'] : null;
            $lat  = isset($data['latitude'])  ? (float) $data['latitude']  : null;
            if (!$lon || !$lat) continue;
            if ($lon < 122.0 || $lon > 123.0 || $lat < -4.5 || $lat > -3.5) continue;

            $features[] = [
                'type'     => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$lon, $lat]],
                'properties' => [
                    'slope'      => $data['slope']      ?? null,
                    'geology'    => $data['geology']    ?? null,
                    'flood_hist' => $data['flood_hist'] ?? null,
                    'label'      => $data['label']      ?? null,
                    'kelurahan'  => null,
                ],
            ];
        }
        fclose($handle);

        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }
}
