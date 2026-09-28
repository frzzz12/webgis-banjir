<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDataBanjir extends Command
{
    protected $signature   = 'import:data-banjir';
    protected $description = 'Import data titik banjir dari CSV ke database';

    public function handle(): int
    {
        $csvPath = base_path('CSV PARAMETER BANJIR/Data Kejadian Banjir.csv');

        if (!file_exists($csvPath)) {
            $this->error("File CSV tidak ditemukan: {$csvPath}");
            return self::FAILURE;
        }

        $this->info('Membaca file CSV…');

        $handle  = fopen($csvPath, 'r');
        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);

        DB::table('titik_banjir')->truncate();

        $batch   = [];
        $count   = 0;
        $skipped = 0;
        $now     = now();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) { $skipped++; continue; }

            $data = array_combine($headers, $row);

            $lon = isset($data['longitude']) ? (float) $data['longitude'] : null;
            $lat = isset($data['latitude'])  ? (float) $data['latitude']  : null;

            if (!$lon || !$lat) { $skipped++; continue; }
            if ($lon < 122.0 || $lon > 123.0 || $lat < -4.5 || $lat > -3.5) { $skipped++; continue; }

            $batch[] = [
                'longitude'        => $lon,
                'latitude'         => $lat,
                'slope'            => isset($data['slope'])      ? (float) $data['slope']      : null,
                'jenis_tanah'      => $data['geology']           ?? null,
                'flood_hist'       => $data['flood_hist']        ?? null,
                'label'            => strtolower(trim($data['label'] ?? '')),
                'geology'          => $data['geology']           ?? null,
                'curah_hujan'      => null,
                'kemiringan'       => null,
                'penggunaan_lahan' => null,
                'drainase_skor'    => null,
                'kelurahan'        => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
            $count++;

            if (count($batch) >= 500) {
                DB::table('titik_banjir')->insert($batch);
                $batch = [];
                $this->output->write('.');
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            DB::table('titik_banjir')->insert($batch);
        }

        $this->newLine();
        $this->info("✅ Import selesai: {$count} titik berhasil, {$skipped} dilewati.");

        // ── Spatial join: tetapkan kelurahan ──────────────────────────
        $this->info('Menjalankan spatial join kelurahan…');
        $updated = $this->assignKelurahan();
        $this->info("✅ Spatial join selesai: {$updated} titik ditetapkan ke kelurahan.");

        // Summary per label
        $summary = DB::table('titik_banjir')
            ->selectRaw('label, COUNT(*) as jumlah')
            ->groupBy('label')
            ->orderBy('jumlah', 'desc')
            ->get();
        $this->table(['Label Risiko', 'Jumlah'], $summary->map(fn($r) => [$r->label, $r->jumlah])->toArray());

        // Summary per kelurahan
        $byKel = DB::table('titik_banjir')
            ->selectRaw('COALESCE(kelurahan, \'Tidak Diketahui\') as kelurahan, COUNT(*) as jumlah')
            ->groupBy('kelurahan')
            ->orderBy('kelurahan')
            ->get();
        $this->table(['Kelurahan', 'Jumlah Titik'], $byKel->map(fn($r) => [$r->kelurahan, $r->jumlah])->toArray());

        return self::SUCCESS;
    }

    /**
     * Lakukan point-in-polygon terhadap setiap titik banjir
     * dan isi kolom kelurahan.
     */
    private function assignKelurahan(): int
    {
        $geojsonPath = public_path('geojson/batas_kelurahan_real.geojson');
        if (!file_exists($geojsonPath)) {
            $geojsonPath = public_path('geojson/batas_kelurahan.geojson');
        }
        if (!file_exists($geojsonPath)) {
            $this->warn('File GeoJSON kelurahan tidak ditemukan, spatial join dilewati.');
            return 0;
        }

        $geojson       = json_decode(file_get_contents($geojsonPath), true);
        $kelurahanList = [];
        foreach ($geojson['features'] as $ft) {
            $kelurahanList[] = [
                'nama'  => $ft['properties']['kelurahan'],
                'rings' => $ft['geometry']['coordinates'],
            ];
        }

        $titik   = DB::table('titik_banjir')->select('id', 'longitude', 'latitude')->get();
        $updated = 0;

        foreach ($titik as $t) {
            $nama = $this->findKelurahan((float) $t->longitude, (float) $t->latitude, $kelurahanList);
            if ($nama !== null) {
                DB::table('titik_banjir')->where('id', $t->id)->update(['kelurahan' => $nama]);
                $updated++;
            }
        }

        return $updated;
    }

    /** Ray-casting point-in-polygon */
    private function pointInPolygon(float $px, float $py, array $ring): bool
    {
        $n      = count($ring);
        $inside = false;
        $j      = $n - 1;
        for ($i = 0; $i < $n; $i++) {
            [$xi, $yi] = $ring[$i];
            [$xj, $yj] = $ring[$j];
            if ((($yi > $py) !== ($yj > $py)) &&
                ($px < ($xj - $xi) * ($py - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        return $inside;
    }

    private function findKelurahan(float $lon, float $lat, array $kelurahanList): ?string
    {
        foreach ($kelurahanList as $kel) {
            foreach ($kel['rings'] as $ring) {
                if ($this->pointInPolygon($lon, $lat, $ring)) {
                    return $kel['nama'];
                }
            }
        }
        return null;
    }
}
