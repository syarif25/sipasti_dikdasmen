<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Log;
use App\Models\TahunAkademik;
use App\Models\JenisSurat;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\SendWaNotification;

class PengajuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Pengajuan::with(['lembaga', 'logs' => function($q) {
            $q->latest();
        }])->orderBy('created_at', 'desc');

        // Filter for school level (Level 1)
        if (auth()->user()->level == 1 && stripos(auth()->user()->name, 'admin') === false) {
            $query->where('id_lembaga', auth()->user()->id_lembaga);
        }

        $pengajuans = $query->get();

        // Filter out completed/archived documents based on the LATEST log
        $pengajuans = $pengajuans->filter(function($p) {
            $latestLog = $p->logs->sortByDesc('id_log')->first();
            if (!$latestLog) return false;

            if ((auth()->user()->level >= 3 || stripos(auth()->user()->name, 'admin') !== false) && $latestLog->status == 'REVISI') {
                return false;
            }

            return !in_array($latestLog->status, ['FINAL', 'SELESAI', 'ARSIP']);
        });

        // For internal structural users (Kasubag, Kabag, KATU, Kabid), ONLY show documents currently at their desk
        if (in_array(auth()->user()->level, [3, 4, 5, 6])) {
            $pengajuans = $pengajuans->filter(function($p) {
                $latestLog = $p->logs->sortByDesc('id_log')->first();
                return strtolower($latestLog->jabatan) == strtolower(auth()->user()->name);
            });
        } elseif (auth()->user()->level >= 7 || stripos(auth()->user()->name, 'admin') !== false) {
            // Admin Dikdasmen only sees documents currently at DIKDASMEN
            $pengajuans = $pengajuans->filter(function($p) {
                $latestLog = $p->logs->sortByDesc('id_log')->first();
                return strtolower($latestLog->posisi) == 'dikdasmen';
            });
        } elseif (auth()->user()->level == 2) {
            // Other Admins (e.g., BPK2M, Bendahara) only see documents currently at their institution
            $pengajuans = $pengajuans->filter(function($p) {
                $latestLog = $p->logs->sortByDesc('id_log')->first();
                return strtolower($latestLog->posisi) == strtolower(auth()->user()->name) || strtolower($latestLog->jabatan) == strtolower(auth()->user()->name);
            });
        }

        $jenisSurats = JenisSurat::all();
        // Fetch active users for destination dropdown in action modal, sorted by level and name
        $usersEselon = User::with('jabatan')
            ->where(function($q) {
                $q->whereIn('level', [3, 4, 5, 6, 7])
                  ->orWhere('name', 'like', '%admin%');
            })
            ->where('status', 1)
            ->orderBy('level', 'desc')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('pengajuan.index', compact('pengajuans', 'jenisSurats', 'usersEselon'));
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
        $lembagas = \App\Models\Lembaga::all();
        
        return view('pengajuan.create', compact('jenisSurats', 'tahunAkademiks', 'jabatans', 'lembagas'));
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
            'file1' => 'required|mimes:pdf|max:51200', // 10MB max
            'file2' => 'nullable|mimes:pdf|max:51200',
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

        // Auto-Routing: Tentukan Posisi Awal
        $posisiTujuan = 'DIKDASMEN';
        $jabatanTujuan = 'administrator';

        // Jika pengirim adalah Admin (level > 1) dan memilih posisi_tujuan
        if ((auth()->user()->level > 1 || stripos(auth()->user()->name, 'admin') !== false || (auth()->user()->lembaga && stripos(auth()->user()->lembaga->nama_lembaga, 'dikdasmen') !== false)) && $request->filled('posisi_tujuan')) {
            $posisiTujuan = $request->posisi_tujuan;
            $jabatanTujuan = $request->posisi_tujuan; // Misal: Bendahara, BPK2M, Sekretariat
        }

        // Create initial Log
        Log::create([
            'id_pengajuan' => $idPengajuan,
            'posisi' => $posisiTujuan,
            'jabatan' => $jabatanTujuan,
            'catatan' => null,
            'tanggal_posisi' => now(),
            'file1' => $file1Path,
            'file2' => $file2Path,
            'status' => 'k',
        ]);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'file1' => 'required|mimes:pdf|max:51200',
            'file2' => 'nullable|mimes:pdf|max:51200',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $latestLog = \App\Models\Log::where('id_pengajuan', $pengajuan->id_pengajuan)->orderBy('id_log', 'desc')->first();
        
        $file1Path = $latestLog ? $latestLog->file1 : null;
        $file2Path = $latestLog ? $latestLog->file2 : null;

        if ($request->hasFile('file1')) {
            $file1Path = $request->file('file1')->store('pengajuan', 'public');
        }
        
        if ($request->hasFile('file2')) {
            $file2Path = $request->file('file2')->store('pengajuan', 'public');
        } elseif ($request->input('file2_option_' . $pengajuan->id_pengajuan) == 'change' && !$request->hasFile('file2')) {
            $file2Path = null;
        }

        Log::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'posisi' => 'DIKDASMEN',
            'jabatan' => 'administrator',
            'catatan' => 'Telah diperbaiki dan diunggah ulang oleh Sekolah/Lembaga',
            'tanggal_posisi' => now(),
            'file1' => $file1Path,
            'file2' => $file2Path,
            'status' => 'k',
        ]);

        return redirect()->back()->with('success', 'Dokumen revisi berhasil dikirim ulang.');
    }

    public function terima(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $latestLog = \App\Models\Log::where('id_pengajuan', $id)->orderBy('id_log', 'desc')->first();

        Log::create([
            'id_pengajuan' => $id,
            'posisi' => $latestLog ? $latestLog->posisi : 'DIKDASMEN',
            'jabatan' => $latestLog ? $latestLog->jabatan : 'administrator',
            'catatan' => $request->catatan,
            'tanggal_posisi' => now(),
            'file1' => $latestLog ? $latestLog->file1 : null,
            'file2' => $latestLog ? $latestLog->file2 : null,
            'status' => 't',
        ]);

        return redirect()->back()->with('success', 'Dokumen telah diterima.');
    }

        public function selesai(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $latestLog = \App\Models\Log::where('id_pengajuan', $id)->orderBy('id_log', 'desc')->first();

        Log::create([
            'id_pengajuan' => $id,
            'posisi' => 'Pengirim',
            'jabatan' => 'Administrator',
            'catatan' => $request->input('catatan', 'Berkas dikembalikan ke pengirim (Selesai diproses)'),
            'tanggal_posisi' => now(),
            'file1' => $latestLog ? $latestLog->file1 : null,
            'file2' => $latestLog ? $latestLog->file2 : null,
            'status' => 'SELESAI',
        ]);

        // === NOTIFIKASI WA — Surat Selesai, Kembali ke Pengirim ===
        $pengajuanObj = \App\Models\Pengajuan::with('lembaga')->find($id);
        $pengirimUser = \App\Models\User::where('id_lembaga', $pengajuanObj->id_lembaga)
            ->where('id_jabatan', '1')->first();
        $ctx = $this->buildCtx($pengajuanObj, 'Pengirim', $request->catatan ?? '');
        $this->kirimWa($pengirimUser, 'Pemberitahuan Surat Telah Selesai Diproses', $ctx);
        // ========================================================================

        return redirect()->back()->with('success', 'Dokumen berhasil diselesaikan dan dikembalikan ke pengirim.');
    }

    public function teruskan(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $latestLog = \App\Models\Log::where('id_pengajuan', $id)->orderBy('id_log', 'desc')->first();

        // Check if Admin (Level 7 or 8) is forwarding a document that has been ACC by Kabid
        $isAdminAndAccKabid = ((auth()->user()->level >= 7 || stripos(auth()->user()->name, 'admin') !== false) && $latestLog && $latestLog->status == 'ACC KABID');

        $rules = [
            'tujuan_user_id' => 'required|string',
            'catatan' => 'nullable|string',
        ];

        if ($isAdminAndAccKabid) {
            $rules['file1'] = 'required|mimes:pdf|max:51200';
            $rules['file2'] = 'nullable|mimes:pdf|max:51200';
        }

        $request->validate($rules);

        // tujuan_user_id can be numeric (id_user) or a string ('BPK2M', 'Admin Dikdasmen', etc)
        $namaTujuan = $request->tujuan_user_id;
        if (is_numeric($namaTujuan)) {
            $userTujuan = User::find($namaTujuan);
            if ($userTujuan) {
                $namaTujuan = $userTujuan->name;
            }
        }

        // Set status
        $status = 'DALAM PROSES';
        if (auth()->user()->level == 6 && strtolower(trim($namaTujuan)) == 'admin dikdasmen') {
            $status = 'ACC KABID';
        }

        // Handle file uploads
        $file1Path = $latestLog ? $latestLog->file1 : null;
        $file2Path = $latestLog ? $latestLog->file2 : null;

        if ($isAdminAndAccKabid) {
            if ($request->hasFile('file1')) {
                $file1Path = $request->file('file1')->store('pengajuan', 'public');
            }
            if ($request->hasFile('file2')) {
                $file2Path = $request->file('file2')->store('pengajuan', 'public');
            }
        }

        // Determine Posisi
        $posisiLog = 'DIKDASMEN';
        if (in_array($namaTujuan, ['BPK2M', 'Bendahara', 'Sekretariat'])) {
            $posisiLog = $namaTujuan;
        }

        Log::create([
            'id_pengajuan' => $id,
            'posisi' => $posisiLog,
            'jabatan' => $namaTujuan,
            'catatan' => $request->catatan,
            'tanggal_posisi' => now(),
            'status' => $status,
            'file1' => $file1Path,
            'file2' => $file2Path,
        ]);

        // === NOTIFIKASI WA — Surat Diteruskan ===
        $posisiText = ($posisiLog == 'DIKDASMEN') ? "DIKDASMEN ({$namaTujuan})" : $posisiLog;
        $pengajuanObj = \App\Models\Pengajuan::with('lembaga')->find($id);
        $ctx = $this->buildCtx($pengajuanObj, $posisiText, $request->catatan ?? '');

        // === Notif ke Penerima Baru ===
        if (in_array($namaTujuan, ['BPK2M', 'Bendahara', 'Sekretariat'])) {
            // Jika ke lembaga Sipasti, ambil nomor HP dari DB Sipasti
            $map = [
                'BPK2M'       => 'LMB0014',
                'Bendahara'   => 'LMB0016',
                'Sekretariat' => 'LMB0015',
            ];
            $idLembagaSipasti = $map[$namaTujuan] ?? null;
            if ($idLembagaSipasti) {
                $adminSipasti = \Illuminate\Support\Facades\DB::connection('sipasti')
                    ->table('user')
                    ->where('id_lembaga', $idLembagaSipasti)
                    ->where('id_jabatan', '1')
                    ->whereNotNull('no_hp')
                    ->first();
                if ($adminSipasti && $adminSipasti->no_hp) {
                    $this->kirimWa( (object)['no_hp' => $adminSipasti->no_hp], 'Pemberitahuan Surat Masuk dari Dikdasmen', $ctx);
                }
            }

            // Notif ke Pengirim Awal bahwa surat telah dilanjutkan ke Lembaga Pusat
            $pengirimUser = \App\Models\User::where('id_lembaga', $pengajuanObj->id_lembaga)
                ->where('id_jabatan', '1')->first();
            $this->kirimWa($pengirimUser, 'Pemberitahuan Posisi Surat Anda', $ctx);
        } else {
            // Internal Dikdasmen (cari user berdasarkan id atau nama)
            $userTujuan = is_numeric($request->tujuan_user_id)
                ? \App\Models\User::find($request->tujuan_user_id)
                : \App\Models\User::where('name', $namaTujuan)->first();
            $this->kirimWa($userTujuan, 'Pemberitahuan Surat Masuk', $ctx);
        }
        // ========================================================================

        return redirect()->back()->with('success', 'Dokumen berhasil diteruskan.');
    }

    public function kembalikan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $latestLog = \App\Models\Log::where('id_pengajuan', $id)->orderBy('id_log', 'desc')->first();
        $file1Path = $latestLog ? $latestLog->file1 : null;
        $file2Path = $latestLog ? $latestLog->file2 : null;

        if (auth()->user()->level == 6) {
            Log::create([
                'id_pengajuan' => $id,
                'posisi' => 'DIKDASMEN',
                'jabatan' => 'administrator',
                'catatan' => $request->catatan,
                'tanggal_posisi' => now(),
                'file1' => $file1Path,
                'file2' => $file2Path,
                'status' => 'KEMBALIKAN KE STAF',
            ]);
            return redirect()->back()->with('success', 'Dokumen dikembalikan ke Staf untuk direvisi ke lembaga.');
        }

        Log::create([
            'id_pengajuan' => $id,
            'posisi' => 'Dikembalikan',
            'jabatan' => 'Sekolah/Lembaga',
            'catatan' => $request->catatan,
            'tanggal_posisi' => now(),
            'file1' => $file1Path,
            'file2' => $file2Path,
            'status' => 'REVISI',
        ]);

        // === NOTIFIKASI WA — Surat Dikembalikan ke Pengirim ===
        $pengajuanObj = \App\Models\Pengajuan::with('lembaga')->find($id);
        $pengirimUser = \App\Models\User::where('id_lembaga', $pengajuanObj->id_lembaga)
            ->where('id_jabatan', '1')->first();
        $ctx = $this->buildCtx($pengajuanObj, 'Pengirim', $request->catatan ?? '');
        $this->kirimWa($pengirimUser, 'Pemberitahuan Surat Dikembalikan untuk Revisi', $ctx);
        // ========================================================================

        return redirect()->back()->with('success', 'Dokumen dikembalikan untuk revisi.');
    }

    public function timeline($id)
    {
        $logs = Log::where('id_pengajuan', $id)->orderBy('created_at', 'asc')->get();
        return response()->json($logs);
    }

    /**
     * =====================================================================
     * NOTIFIKASI WA — Internal Dikdasmen
     * Mengirim pesan WA background ke nomor tujuan tertentu.
     * =====================================================================
     */
    private function kirimWa($targetUser, string $title, array $ctx = []): void
    {
        if (empty($targetUser?->no_hp)) return;

        $pengirimLembaga = $ctx['pengirim_lembaga'] ?? '-';
        $tujuan          = $ctx['tujuan']          ?? '-';
        $jenisSurat      = $ctx['jenis_surat']     ?? '-';
        $perihal         = $ctx['perihal']          ?? '-';
        $posisiSekarang  = $ctx['posisi']           ?? '-';
        $catatan         = $ctx['catatan']          ?? '';
        $waktu           = now()->format('Y-m-d H:i:s');

        $pesan = "*{$title}*\n\n"
               . "Pengirim   : {$pengirimLembaga}\n"
               . "Tujuan     : {$tujuan}\n"
               . "Posisi     : *{$posisiSekarang}*\n"
               . "Jenis Surat: {$jenisSurat}\n"
               . "Perihal    : {$perihal}\n";

        if (!empty($catatan)) {
            $pesan .= "\n*CATATAN: {$catatan}*\n";
        }

        $pesan .= "Waktu      : {$waktu}\n\n"
               . "Silakan login ke www.com dengan login menggunakan username dan password yang valid.\n\n"
               . "Hajah Khidmah, ikhtiar menjadi lebih baik";

        SendWaNotification::dispatch($targetUser->no_hp, $pesan);
    }

    private function buildCtx($pengajuan, string $posisiSekarang, string $catatan = ''): array
    {
        $pengajuan->loadMissing('lembaga');
        return [
            'pengirim_lembaga' => $pengajuan->lembaga?->nama_lembaga ?? '-',
            'tujuan'           => $pengajuan->tujuan ?? '-',
            'jenis_surat'      => $pengajuan->jenis_surat ?? '-',
            'perihal'          => $pengajuan->perihal ?? '-',
            'posisi'           => $posisiSekarang,
            'catatan'          => $catatan,
        ];
    }

}