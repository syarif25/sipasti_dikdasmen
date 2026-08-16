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
        // Get all pengajuan
        $pengajuans = Pengajuan::with(['lembaga', 'logs' => function($q) {
            $q->latest();
        }])->get();

        $jenisSurats = JenisSurat::all();
        
        return view('arsip.index', compact('pengajuans', 'jenisSurats'));
    }
}
