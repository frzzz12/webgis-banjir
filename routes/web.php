<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataBanjirController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PrediksiController;

// ══════════════════════════════════════════════
//  HALAMAN PUBLIK - 1 Halaman Peta
// ══════════════════════════════════════════════
Route::get('/', function () {
    return view('welcome');
})->name('home');

// API GeoJSON (publik)
Route::get('/api/geojson/batas', [MapController::class, 'geojsonBatas'])->name('api.geojson.batas');
Route::get('/api/geojson/titik-banjir', [MapController::class, 'geojsonTitikBanjir'])->name('api.geojson.titik');
Route::get('/api/geojson/kelurahan', [MapController::class, 'geojsonKelurahan'])->name('api.geojson.kelurahan');
Route::get('/api/geojson/zona-risiko', [MapController::class, 'geojsonZonaRisiko'])->name('api.geojson.zona');


// ══════════════════════════════════════════════
//  AUTH
// ══════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

// ══════════════════════════════════════════════
//  ADMIN AREA (harus login)
// ══════════════════════════════════════════════
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Data Banjir
    Route::get('/data-banjir', [DataBanjirController::class, 'index'])->name('data-banjir.index');
    Route::delete('/data-banjir/{id}', [DataBanjirController::class, 'destroy'])->name('data-banjir.destroy');
    Route::post('/data-banjir/import', [DataBanjirController::class, 'importCsv'])->name('data-banjir.import');

    // Prediksi
    Route::get('/prediksi', [PrediksiController::class, 'index'])->name('prediksi');
    Route::post('/prediksi/proses', [PrediksiController::class, 'proses'])->name('prediksi.proses');
    Route::post('/prediksi/api', [PrediksiController::class, 'apiPrediksi'])->name('prediksi.api');

});

// Redirect lama /peta & /dashboard ke halaman baru
Route::redirect('/peta', '/')->name('peta');
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');
Route::redirect('/prediksi', '/admin/prediksi')->name('prediksi');
