@extends('layouts.admin')
@section('title', 'Data Titik Banjir')
@section('page-title', 'Data Titik Banjir')

@push('styles')
<style>
    .filter-bar {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        background: #fff; padding: 16px 20px;
        border-radius: 14px; border: 1px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        margin-bottom: 20px;
    }
    .filter-bar label { font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; }
    .filter-select, .filter-input {
        padding: 7px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; color: #1e293b; background: #f8fafc;
        outline: none; transition: border-color .15s;
    }
    .filter-select:focus, .filter-input:focus { border-color: #1a73e8; background: #fff; }
    .filter-input { min-width: 200px; }
    .btn-filter {
        padding: 7px 16px; background: #1a73e8; color: white;
        border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
        cursor: pointer; transition: all .15s; display: flex; align-items: center; gap: 6px;
    }
    .btn-filter:hover { background: #1557b0; }
    .btn-reset {
        padding: 7px 14px; background: #f8fafc; color: #64748b;
        border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px;
        cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px;
        transition: all .15s;
    }
    .btn-reset:hover { background: #f1f5f9; }
    .total-badge {
        margin-left: auto; background: #eff6ff; color: #1a73e8;
        border: 1px solid #bfdbfe; border-radius: 8px;
        padding: 5px 12px; font-size: 12px; font-weight: 700;
    }

    .card {
        background: #fff; border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 1px solid #f1f5f9;
    }
    .card-header {
        padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .card-header h3 { font-size: 14px; font-weight: 700; color: #1e293b; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { padding: 10px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; border-bottom: 1px solid #f1f5f9; text-align: left; background: #fafafa; }
    td { padding: 11px 16px; border-bottom: 1px solid #f8fafc; color: #374151; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f8fafc; }

    .risk-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11.5px; font-weight: 700;
    }
    .risk-tinggi        { background: #fff1f2; color: #be123c; }
    .risk-sedang        { background: #fff7ed; color: #c2410c; }
    .risk-rendah        { background: #fefce8; color: #a16207; }
    .risk-sangat-rendah { background: #f0fdf4; color: #15803d; }

    .btn-delete {
        padding: 5px 11px; background: #fff1f2;
        border: 1px solid #fecdd3; color: #be123c;
        border-radius: 7px; font-size: 12px; cursor: pointer;
        transition: all .15s;
    }
    .btn-delete:hover { background: #ffe4e6; }

    .pagination-wrap {
        padding: 16px 20px; border-top: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
        font-size: 12.5px; color: #64748b;
    }
    .pagination { display: flex; gap: 4px; }
    .pagination a, .pagination span {
        padding: 5px 10px; border-radius: 6px; border: 1px solid #e2e8f0;
        font-size: 12.5px; text-decoration: none; color: #374151;
        transition: all .12s;
    }
    .pagination a:hover { background: #eff6ff; border-color: #bfdbfe; color: #1a73e8; }
    .pagination span.active { background: #1a73e8; border-color: #1a73e8; color: #fff; font-weight: 700; }
    .pagination span.disabled { opacity: .4; pointer-events: none; }
</style>
@endpush

@section('content')

<!-- Filter Bar -->
<form method="GET" action="{{ route('admin.data-banjir.index') }}">
    <div class="filter-bar">
        <div>
            <label>Label Risiko</label><br>
            <select name="label" class="filter-select" style="margin-top:4px">
                <option value="">Semua</option>
                <option value="tinggi"        {{ request('label')=='tinggi'        ? 'selected':'' }}>Tinggi</option>
                <option value="sedang"        {{ request('label')=='sedang'        ? 'selected':'' }}>Sedang</option>
                <option value="rendah"        {{ request('label')=='rendah'        ? 'selected':'' }}>Rendah</option>
                <option value="sangat rendah" {{ request('label')=='sangat rendah' ? 'selected':'' }}>Sangat Rendah</option>
            </select>
        </div>
        <div>
            <label>Cari</label><br>
            <input type="text" name="search" class="filter-input" style="margin-top:4px"
                placeholder="Riwayat banjir, jenis tanah…" value="{{ request('search') }}">
        </div>
        <div style="align-self:flex-end;display:flex;gap:6px">
            <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
            <a href="{{ route('admin.data-banjir.index') }}" class="btn-reset"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        </div>
        <div class="total-badge">
            <i class="fa-solid fa-database" style="margin-right:5px"></i>
            {{ number_format($total) }} total titik
        </div>
    </div>
</form>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-location-dot" style="color:#1a73e8;margin-right:8px"></i>
            Titik Data Banjir
            <span style="font-weight:400;color:#64748b;font-size:12px;margin-left:6px">({{ $data->total() }} hasil)</span>
        </h3>
        <form method="POST" action="{{ route('admin.data-banjir.import') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn-filter" style="padding:6px 14px;font-size:12px"
                onclick="return confirm('Import ulang CSV? Data lama akan ditimpa.')">
                <i class="fa-solid fa-file-import"></i> Import CSV
            </button>
        </form>
    </div>

    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Riwayat Banjir</th>
                    <th>Geologi</th>
                    <th>Kemiringan</th>
                    <th>Label Risiko</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
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
                    <td style="color:#94a3b8;font-size:12px">{{ $row->id }}</td>
                    <td style="font-family:monospace;font-size:12px">{{ number_format($row->latitude, 6) }}</td>
                    <td style="font-family:monospace;font-size:12px">{{ number_format($row->longitude, 6) }}</td>
                    <td>{{ $row->flood_hist ?? '-' }}</td>
                    <td style="font-size:12px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $row->geology ?? '' }}">
                        {{ Str::limit($row->geology ?? '-', 30) }}
                    </td>
                    <td>{{ $row->slope ? number_format($row->slope, 2).'°' : '-' }}</td>
                    <td><span class="risk-badge {{ $labelClass }}">{{ ucfirst($row->label ?? '-') }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('admin.data-banjir.destroy', $row->id) }}"
                            onsubmit="return confirm('Hapus data ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:40px">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        <span>Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data</span>
        <div class="pagination">
            {{ $data->links('pagination::simple-default') }}
        </div>
    </div>
</div>

@endsection
