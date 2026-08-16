<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\JenisSurat;
use Illuminate\Http\Request;

class ArsipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get pengajuan where latest log status IS Final/Selesai/Arsip
        $pengajuans = Pengajuan::with(['lembaga', 'logs' => function($q) {
            $q->latest();
        }])->whereHas('logs', function($q) {
            $q->whereIn('status', ['FINAL', 'SELESAI', 'ARSIP']);
        })->get();

        $jenisSurats = JenisSurat::all();
        
        return view('arsip.index', compact('pengajuans', 'jenisSurats'));
    }
}
