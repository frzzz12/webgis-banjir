<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $statistik = json_decode(file_get_contents(public_path('data/statistik_data.json')), true);
        return view('dashboard.index', compact('statistik'));
    }
}
