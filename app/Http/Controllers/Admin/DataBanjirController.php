<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataBanjirController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('titik_banjir');

        if ($request->filled('label')) {
            $query->where('label', $request->label);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('flood_hist', 'like', '%'.$request->search.'%')
                  ->orWhere('jenis_tanah', 'like', '%'.$request->search.'%');
            });
        }

        $data  = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $total = DB::table('titik_banjir')->count();

        return view('admin.data-banjir.index', compact('data', 'total'));
    }

    public function destroy($id)
    {
        DB::table('titik_banjir')->where('id', $id)->delete();
        return back()->with('success', 'Data berhasil dihapus.');
    }

    public function importCsv()
    {
        try {
            \Artisan::call('import:data-banjir');
            $output = \Artisan::output();
            return back()->with('success', 'Import CSV berhasil! '.$output);
        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: '.$e->getMessage());
        }
    }
}
