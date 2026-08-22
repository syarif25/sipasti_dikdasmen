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
        $query = Pengajuan::with(['lembaga', 'logs' => function($q) {
            $q->latest();
        }]);

        // Filter for school level (Level 1)
        if (auth()->user()->level == 1) {
            $query->where('id_lembaga', auth()->user()->id_lembaga);
        }

        $pengajuans = $query->get();

        // Filter for completed/archived documents ONLY
        $pengajuans = $pengajuans->filter(function($p) {
            $latestLog = $p->logs->sortByDesc('id_log')->first();
            if (!$latestLog) return false; // Ignore corrupted data with no logs
            
            return in_array($latestLog->status, ['FINAL', 'SELESAI', 'ARSIP', 'DIARSIP']);
        });

        $jenisSurats = JenisSurat::all();
        
        return view('arsip.index', compact('pengajuans', 'jenisSurats'));
    }
}
