<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Log;
use App\Models\TahunAkademik;
use App\Models\JenisSurat;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PengajuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get pengajuan where latest log status is NOT Final/Selesai/Arsip
        $pengajuans = Pengajuan::with(['lembaga', 'logs' => function($q) {
            $q->latest();
        }])->whereHas('logs', function($q) {
            $q->whereNotIn('status', ['FINAL', 'SELESAI', 'ARSIP']);
        })->get();

        $jenisSurats = JenisSurat::all();
        $jabatans = Jabatan::all(); // for destination dropdown in action modal
        
        return view('pengajuan.index', compact('pengajuans', 'jenisSurats', 'jabatans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jenisSurats = JenisSurat::all();
        $tahunAkademiks = TahunAkademik::where('status', '1')->get();
        // Fallback if no active tahun akademik
        if($tahunAkademiks->isEmpty()) {
            $tahunAkademiks = TahunAkademik::all();
        }
        
        $jabatans = Jabatan::all();
        
        return view('pengajuan.create', compact('jenisSurats', 'tahunAkademiks', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:100',
            'jenis_surat' => 'required|string',
            'perihal' => 'required|string',
            'tujuan' => 'required|string',
            'file1' => 'required|mimes:pdf|max:10240', // 10MB max
            'file2' => 'nullable|mimes:pdf|max:10240',
        ]);

        $activeYear = TahunAkademik::where('status', 'Aktif')->first();
        $idTahun = $activeYear ? $activeYear->id_tahun : 2025; // fallback

        $idPengajuan = 'PGJ' . time();

        $pengajuan = new Pengajuan();
        $pengajuan->id_pengajuan = $idPengajuan;
        $pengajuan->nomor_surat = $request->nomor_surat;
        $pengajuan->jenis_surat = $request->jenis_surat;
        $pengajuan->perihal = $request->perihal;
        $pengajuan->tujuan = collect(Jabatan::where('id_jabatan', $request->tujuan)->first())->get('nama_jabatan', $request->tujuan);
        $pengajuan->ket = $request->ket;
        $pengajuan->tgl_upload = now();
        $pengajuan->id_tahun = $idTahun;
        $pengajuan->pencairan = '-';
        $pengajuan->lpj = 0;
        $pengajuan->id_lembaga = auth()->user()->id_lembaga;
        $pengajuan->user_id = auth()->id();
        $pengajuan->save();

        // Handle File Uploads
        $file1Path = null;
        $file2Path = null;

        if ($request->hasFile('file1')) {
            $file1Path = $request->file('file1')->store('pengajuan', 'public');
        }
        if ($request->hasFile('file2')) {
            $file2Path = $request->file('file2')->store('pengajuan', 'public');
        }

        // Create initial Log
        Log::create([
            'id_pengajuan' => $idPengajuan,
            'posisi' => auth()->user()->lembaga->nama_lembaga ?? 'Sekolah',
            'jabatan' => 'Pengunggah',
            'catatan' => 'Pengajuan awal diunggah.',
            'tanggal_posisi' => now(),
            'file1' => $file1Path,
            'file2' => $file2Path,
            'status' => 'DALAM PROSES',
        ]);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil ditambahkan!');
    }

    public function terima(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        Log::create([
            'id_pengajuan' => $id,
            'posisi' => 'Diterima',
            'jabatan' => auth()->user()->jabatan->nama_jabatan ?? 'Staf',
            'catatan' => $request->catatan,
            'tanggal_posisi' => now(),
            'status' => 'DALAM PROSES',
        ]);

        return redirect()->back()->with('success', 'Dokumen telah diterima.');
    }

    public function teruskan(Request $request, $id)
    {
        $request->validate([
            'tujuan_jabatan' => 'required|string',
            'catatan' => 'nullable|string',
            'is_final' => 'nullable|boolean'
        ]);

        $jabatanTujuan = Jabatan::where('id_jabatan', $request->tujuan_jabatan)->first();
        $namaTujuan = $jabatanTujuan ? $jabatanTujuan->nama_jabatan : $request->tujuan_jabatan;

        $status = $request->is_final ? 'SELESAI' : 'DALAM PROSES';

        Log::create([
            'id_pengajuan' => $id,
            'posisi' => 'DIKTI',
            'jabatan' => $namaTujuan,
            'catatan' => $request->catatan,
            'tanggal_posisi' => now(),
            'status' => $status,
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil diteruskan.');
    }

    public function kembalikan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string',
        ]);

        Log::create([
            'id_pengajuan' => $id,
            'posisi' => 'Dikembalikan',
            'jabatan' => 'Sekolah/Lembaga',
            'catatan' => $request->catatan,
            'tanggal_posisi' => now(),
            'status' => 'REVISI',
        ]);

        return redirect()->back()->with('success', 'Dokumen dikembalikan untuk revisi.');
    }

    public function timeline($id)
    {
        $logs = Log::where('id_pengajuan', $id)->orderBy('created_at', 'desc')->get();
        return response()->json($logs);
    }
}
