<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Jobs\SendWaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntegrasiSipastiController extends Controller
{
    private function resolveRole(?string $userName): string {
        $userName = (string) $userName;
        if (stripos($userName, "bpk2m") !== false) return "BPK2M";
        if (stripos($userName, "bendahara") !== false) return "Bendahara";
        if (stripos($userName, "sekretariat") !== false) return "Sekretariat";
        if (stripos($userName, "dikdasmen") !== false) return "Dikdasmen";
        return "BPK2M"; // fallback default
    }

    public function getPengajuanSelesai(Request $request)
    {
        $userName = $request->input("user_name", "");
        $posisiFilter = $this->resolveRole($userName);

        // Query identik dengan Sipasti: JOIN ke log terkini, filter berdasarkan posisi
        $query = Pengajuan::select(
                "pengajuans.*",
                "log.posisi as posisi_sekarang",
                "log.jabatan as jabatan_sekarang",
                "log.status as status_sekarang",
                "log.tanggal_posisi as maxdate",
                "log.file1 as file1_log",
                "log.file2 as file2_log",
                "log.id_log as id_log_latest",
                DB::raw("lembaga.nama_lembaga as nama_lembaga"),
                DB::raw("lembaga.singkatan_lembaga as singkatan_lembaga")
            )
            ->join(
                DB::raw("(SELECT id_pengajuan, MAX(id_log) as max_log_id, MAX(tanggal_posisi) as maxdate FROM logs GROUP BY id_pengajuan) as x"),
                "pengajuans.id_pengajuan", "=", "x.id_pengajuan"
            )
            ->join("logs as log", "log.id_log", "=", "x.max_log_id")
            ->leftJoin("lembagas as lembaga", "lembaga.id_lembaga", "=", "pengajuans.id_lembaga")
            ->where("log.posisi", $posisiFilter)
            ->whereNotIn("log.status", ["AR"]);

        // Filter pencarian
        if ($request->filled("perihal")) {
            $query->where("pengajuans.perihal", "like", "%".$request->perihal."%");
        }
        if ($request->filled("pengirim")) {
            $query->where("lembaga.singkatan_lembaga", "like", "%".$request->pengirim."%");
        }
        if ($request->filled("posisi")) {
            $query->where("log.posisi", "like", "%".$request->posisi."%");
        }
        if ($request->filled("tujuan")) {
            $query->where("pengajuans.tujuan", "like", "%".$request->tujuan."%");
        }
        if ($request->filled("tanggal")) {
            try {
                $tanggal = \Carbon\Carbon::parse($request->tanggal)->format("Y-m-d");
                $query->whereDate("log.tanggal_posisi", $tanggal);
            } catch (\Exception $e) {}
        }

        $sortField = $request->input("sort_field", "tanggal_posisi");
        $sortDir = strtolower($request->input("sort_dir", "desc")) === "asc" ? "asc" : "desc";
        $sortMap = [
            "tanggal_posisi" => "log.tanggal_posisi",
            "perihal" => "pengajuans.perihal",
            "pengirim" => "lembaga.singkatan_lembaga",
        ];
        $mappedSort = $sortMap[$sortField] ?? "log.tanggal_posisi";

        $paginator = $query->orderBy($mappedSort, $sortDir)->paginate($request->get("per_page", 10));

        // Format respons agar sama dengan Sipasti (available_actions)
        $paginator->getCollection()->transform(function ($p) use ($posisiFilter) {
            $stat = strtolower($p->status_sekarang ?? "");
            $jenis = strtolower($p->jenis_surat ?? "");
            $tujuan = strtolower($p->tujuan ?? "");
            $posisiLower = strtolower($posisiFilter);
            $actions = [];

            if ($stat === "k" || $stat === "" || ($stat !== "t" && !str_starts_with($stat, "kmb"))) {
                $actions[] = "terima";
            } else if ($stat === "t") {
                if ($posisiLower === "bpk2m") {
                    if ($tujuan === 'bpk2m' && $jenis === 'pemberitahuan') {
                        $actions = ["arsip", "kembali"];
                    } else if ($tujuan === 'bpk2m') {
                        $actions = ["finish", "kembali"];
                    } else {
                        $actions = ["lanjut_dikti", "kembali"];
                    }
                } else if ($posisiLower === "bendahara") {
                    if ($tujuan === 'bendahara' && $jenis === 'pemberitahuan') {
                        $actions = ["arsip", "kembali"];
                    } else {
                        $actions = ["finish", "kembali"];
                    }
                } else if ($posisiLower === "sekretariat") {
                    if ($jenis === 'pemberitahuan') {
                        $actions = ["arsip", "kembali"];
                    } else if ($tujuan !== 'sekretariat') {
                        $actions = ["lanjut", "kembali", "kirim_pesan"];
                    } else {
                        $actions = ["lanjut", "kembali"]; 
                    }
                } else {
                    $actions = ["lanjut", "kembali"];
                }
            } else if ($stat === "fn") {
                if ($posisiLower === "dikdasmen") {
                    $actions = ["finish"];
                }
            } else if (str_starts_with($stat, "kmb")) {
                $actions = [];
            }

            $p->available_actions = $actions;
            $p->latest_log = [
                "posisi" => $p->posisi_sekarang,
                "jabatan" => $p->jabatan_sekarang,
                "status" => $p->status_sekarang,
                "tanggal_posisi" => $p->maxdate,
                "file1" => $p->file1_log,
                "file2" => $p->file2_log,
                "id_log" => $p->id_log_latest,
            ];
            return $p;
        });

        return response()->json($paginator);
    }

    private function getLatestLog($id) {
        return \App\Models\Log::where("id_pengajuan", $id)->orderBy("id_log", "desc")->first();
    }

    public function arsip(Request $request)
    {
        $userName = $request->input("user_name", "");
        $posisiFilter = $this->resolveRole($userName);

        // Query identik dengan Sipasti: JOIN ke log terkini, filter berdasarkan posisi
        $query = Pengajuan::select(
                "pengajuans.*",
                "log.posisi as posisi_sekarang",
                "log.jabatan as jabatan_sekarang",
                "log.status as status_sekarang",
                "log.tanggal_posisi as maxdate",
                "log.file1 as file1_log",
                "log.file2 as file2_log",
                "log.id_log as id_log_latest",
                DB::raw("lembaga.nama_lembaga as nama_lembaga"),
                DB::raw("lembaga.singkatan_lembaga as singkatan_lembaga")
            )
            ->join(
                DB::raw("(SELECT id_pengajuan, MAX(id_log) as max_log_id, MAX(tanggal_posisi) as maxdate FROM logs GROUP BY id_pengajuan) as x"),
                "pengajuans.id_pengajuan", "=", "x.id_pengajuan"
            )
            ->join("logs as log", "log.id_log", "=", "x.max_log_id")
            ->leftJoin("lembagas as lembaga", "lembaga.id_lembaga", "=", "pengajuans.id_lembaga")
            /* No filter for posisi or status to match Sipasti Arsip */ ;

        // Filter pencarian
        if ($request->filled("perihal")) {
            $query->where("pengajuans.perihal", "like", "%".$request->perihal."%");
        }
        if ($request->filled("pengirim")) {
            $query->where("lembaga.singkatan_lembaga", "like", "%".$request->pengirim."%");
        }
        if ($request->filled("posisi")) {
            $query->where("log.posisi", "like", "%".$request->posisi."%");
        }
        if ($request->filled("tujuan")) {
            $query->where("pengajuans.tujuan", "like", "%".$request->tujuan."%");
        }
        if ($request->filled("tanggal")) {
            try {
                $tanggal = \Carbon\Carbon::parse($request->tanggal)->format("Y-m-d");
                $query->whereDate("log.tanggal_posisi", $tanggal);
            } catch (\Exception $e) {}
        }

        $sortField = $request->input("sort_field", "tanggal_posisi");
        $sortDir = strtolower($request->input("sort_dir", "desc")) === "asc" ? "asc" : "desc";
        $sortMap = [
            "tanggal_posisi" => "log.tanggal_posisi",
            "perihal" => "pengajuans.perihal",
            "pengirim" => "lembaga.singkatan_lembaga",
        ];
        $mappedSort = $sortMap[$sortField] ?? "log.tanggal_posisi";

        $paginator = $query->orderBy($mappedSort, $sortDir)->paginate($request->get("per_page", 10));

        // Format respons agar sama dengan Sipasti (available_actions)
        $paginator->getCollection()->transform(function ($p) use ($posisiFilter) {
            $stat = strtolower($p->status_sekarang ?? "");
            $jenis = strtolower($p->jenis_surat ?? "");
            $tujuan = strtolower($p->tujuan ?? "");
            $posisiLower = strtolower($posisiFilter);
            $actions = [];

            if ($stat === "k" || $stat === "" || ($stat !== "t" && !str_starts_with($stat, "kmb"))) {
                $actions[] = "terima";
            } else if ($stat === "t") {
                if ($posisiLower === "bpk2m") {
                    if ($tujuan === 'bpk2m' && $jenis === 'pemberitahuan') {
                        $actions = ["arsip", "kembali"];
                    } else if ($tujuan === 'bpk2m') {
                        $actions = ["finish", "kembali"];
                    } else {
                        $actions = ["lanjut_dikti", "kembali"];
                    }
                } else if ($posisiLower === "bendahara") {
                    if ($tujuan === 'bendahara' && $jenis === 'pemberitahuan') {
                        $actions = ["arsip", "kembali"];
                    } else {
                        $actions = ["finish", "kembali"];
                    }
                } else if ($posisiLower === "sekretariat") {
                    if ($jenis === 'pemberitahuan') {
                        $actions = ["arsip", "kembali"];
                    } else if ($tujuan !== 'sekretariat') {
                        $actions = ["lanjut", "kembali", "kirim_pesan"];
                    } else {
                        $actions = ["lanjut", "kembali"]; 
                    }
                } else {
                    $actions = ["lanjut", "kembali"];
                }
            } else if ($stat === "fn") {
                if ($posisiLower === "dikdasmen") {
                    $actions = ["finish"];
                }
            } else if (str_starts_with($stat, "kmb")) {
                $actions = [];
            }

            $p->available_actions = $actions;
            $p->latest_log = [
                "posisi" => $p->posisi_sekarang,
                "jabatan" => $p->jabatan_sekarang,
                "status" => $p->status_sekarang,
                "tanggal_posisi" => $p->maxdate,
                "file1" => $p->file1_log,
                "file2" => $p->file2_log,
                "id_log" => $p->id_log_latest,
            ];
            return $p;
        });

        return response()->json($paginator);
    }

    private function resolveUser($request) {
        return $this->resolveRole($request->input("user_name", "BPK2M"));
    }

    public function terima(Request $request, $id)
    {
        Pengajuan::findOrFail($id);
        $latestLog = $this->getLatestLog($id);
        $posisi = $this->resolveUser($request);

        \App\Models\Log::create([
            "id_pengajuan" => $id,
            "posisi" => $posisi,
            "jabatan" => "Administrator",  // selalu Administrator, sama seperti Sipasti
            "catatan" => $request->input("catatan", "-"),
            "tanggal_posisi" => now(),
            "file1" => $latestLog ? $latestLog->file1 : null,
            "file2" => $latestLog ? $latestLog->file2 : null,
            "status" => "t",
        ]);

        return response()->json(["message" => "Diterima"]);
    }

    public function arsipBerkas(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $latestLog = $this->getLatestLog($id);
        $posisi = $this->resolveUser($request);
        
        \App\Models\Log::create([
            "id_pengajuan" => $id,
            "posisi" => $posisi,
            "jabatan" => "Administrator",  // selalu Administrator, sama seperti Sipasti
            "catatan" => "Diarsipkan",
            "tanggal_posisi" => now(),
            "file1" => $latestLog ? $latestLog->file1 : null,
            "file2" => $latestLog ? $latestLog->file2 : null,
            "status" => "AR",
        ]);
        
        return response()->json(["message" => "Arsip Berhasil"]);
    }

    /**
     * Helper: Lookup user tujuan dari DB Sipasti, ambil singkatan_lembaga & nama_jabatan.
     * Identik dengan logika lanjutkan() di PengajuanController Sipasti.
     */
    private function getSipastiUserInfo(int $idUserTujuan): array
    {
        try {
            $user = DB::connection('sipasti')
                ->table('user')
                ->join('lembaga', 'user.id_lembaga', '=', 'lembaga.id_lembaga')
                ->join('jabatan', 'user.id_jabatan', '=', 'jabatan.id_jabatan')
                ->where('user.id_user', $idUserTujuan)
                ->select('lembaga.singkatan_lembaga', 'jabatan.nama_jabatan', 'user.id_lembaga')
                ->first();

            if ($user) {
                return [
                    'posisi'     => $user->singkatan_lembaga ?? 'Unknown',
                    'jabatan'    => $user->nama_jabatan ?? 'Administrator',
                    'id_lembaga' => $user->id_lembaga ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Sitaksi: Gagal lookup Sipasti user #' . $idUserTujuan . ': ' . $e->getMessage());
        }
        return ['posisi' => 'Unknown', 'jabatan' => 'Administrator', 'id_lembaga' => null];
    }

    public function lanjutDikti(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $latestLog = $this->getLatestLog($id);

        // Lookup user tujuan dari DB Sipasti — identik dengan Sipasti lanjutkan()
        $idUserTujuan = (int) $request->input('id_user_tujuan', 0);
        $sipastiUser  = $idUserTujuan ? $this->getSipastiUserInfo($idUserTujuan) : null;

        // Posisi = singkatan_lembaga tujuan (Bendahara, Sekretariat, Pengasuh, dst)
        $posisiTujuan  = $sipastiUser['posisi']  ?? $request->input('tujuan_jabatan', 'BPK2M');
        $jabatanTujuan = $sipastiUser['jabatan'] ?? $request->input('tujuan_jabatan', 'BPK2M');

        $file1 = $latestLog ? $latestLog->file1 : null;
        if ($request->hasFile('file1')) {
            $file1 = $request->file('file1')->store('uploads/pengajuan', 'public');
        }

        $file2 = $latestLog ? $latestLog->file2 : null;
        if ($request->input('perubahan_file') === 'ya' && $request->hasFile('file2')) {
            $file2 = $request->file('file2')->store('uploads/pengajuan', 'public');
        }

        \App\Models\Log::create([
            'id_pengajuan'   => $id,
            'posisi'         => $posisiTujuan,
            'jabatan'        => $jabatanTujuan,
            'catatan'        => $request->input('catatan', '-'),
            'tanggal_posisi' => now(),
            'file1'          => $file1,
            'file2'          => $file2,
            'status'         => 'k', // lintas lembaga = kirim
        ]);

        return response()->json(['message' => 'Dilanjutkan via Lanjut Dikti ke ' . $posisiTujuan]);
    }

    public function lanjut(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $latestLog = $this->getLatestLog($id);
        $posisiPengirim = $this->resolveUser($request); // BPK2M, Bendahara, Sekretariat

        // Lookup user tujuan dari DB Sipasti — identik dengan Sipasti lanjutkan()
        $idUserTujuan = (int) $request->input('tujuan', 0);
        $sipastiUser  = $idUserTujuan ? $this->getSipastiUserInfo($idUserTujuan) : null;

        // Posisi = singkatan_lembaga (Bendahara, Sekretariat, dll)
        // tujuan_label dikirim frontend dan berisi nama lembaga dari dropdown
        $posisiTujuan  = ($sipastiUser && $sipastiUser['posisi'] !== 'Unknown')
            ? $sipastiUser['posisi']
            : $request->input('tujuan_label', $request->input('tujuan_jabatan', 'Unknown'));
        $jabatanTujuan = ($sipastiUser && $sipastiUser['jabatan'])
            ? $sipastiUser['jabatan']
            : $request->input('tujuan_jabatan', 'Administrator');
        $idLembagaTujuan = $sipastiUser['id_lembaga'] ?? null;

        // Tentukan status: 'k' (kirim) jika lintas lembaga, '' jika sesama lembaga
        $lembagaMap = [
            'BPK2M'       => 'LMB0014',
            'Bendahara'   => 'LMB0016',
            'Sekretariat' => 'LMB0015',
        ];
        $idLembagaPengirim = $lembagaMap[$posisiPengirim] ?? null;
        $isCrossLembaga = ($idLembagaTujuan && $idLembagaPengirim && $idLembagaTujuan !== $idLembagaPengirim);
        $statusLog = $isCrossLembaga ? 'k' : '';

        \App\Models\Log::create([
            'id_pengajuan'   => $id,
            'posisi'         => $posisiTujuan,
            'jabatan'        => $jabatanTujuan,
            'catatan'        => $request->input('catatan', '-'),
            'tanggal_posisi' => now(),
            'file1'          => $latestLog ? $latestLog->file1 : null,
            'file2'          => $latestLog ? $latestLog->file2 : null,
            'status'         => $statusLog,
        ]);

        // === NOTIFIKASI WA: Surat Dilanjutkan ===
        try {
            $pengajuanObj = Pengajuan::with('lembaga')->find($id);
            $ctx = $this->buildWaCtx($pengajuanObj, $posisiTujuan, $request->input('catatan', ''));
            // Notif ke penerima baru di sipasti
            $noHpTujuan = $this->getAdminNoHp($posisiTujuan);
            $this->kirimWaDikdasmen($noHpTujuan, 'Pemberitahuan Surat Masuk ke Meja Anda', $ctx);
            // Notif ke pengirim awal surat
            $noHpPengirim = $this->getPengirimNoHp($pengajuanObj);
            $this->kirimWaDikdasmen($noHpPengirim, 'Pemberitahuan Surat Anda Sedang Diproses', $ctx);
        } catch (\Exception $e) { /* Notif WA gagal tidak boleh hentikan proses */ }
        // ========================================================

        return response()->json(['message' => 'Dilanjutkan ke ' . $posisiTujuan]);
    }

    public function kembali(Request $request, $id)
    {
        Pengajuan::findOrFail($id);
        $latestLog   = $this->getLatestLog($id);
        $posisiPengirim = $this->resolveUser($request); // BPK2M, Bendahara, Sekretariat
        $tujuan = $request->input('tujuan', 'pengirim');

        // Sama persis dengan Sipasti kembalikan():
        // - 'pengirim' → KMB3 (dikembalikan ke sekolah pengirim)
        // - lainnya    → KMBD (dikembalikan ke admin internal BPK2M/lembaga)
        // Coba lookup DB Sipasti jika tujuan berupa angka (id_user)
        $idUserTujuan = is_numeric($tujuan) ? (int) $tujuan : 0;

        if ($tujuan === 'pengirim' || $tujuan === 'Sekolah/Lembaga') {
            $posisiTujuan  = 'Pengirim';
            $jabatanTujuan = 'Administrator';
            $statusRevisi  = 'KMB3';
        } elseif ($idUserTujuan > 0) {
            // Kembalikan ke admin lembaga tertentu (misal BPK2M dari Bendahara)
            $sipastiUser  = $this->getSipastiUserInfo($idUserTujuan);
            $posisiTujuan  = ($sipastiUser['posisi'] !== 'Unknown') ? $sipastiUser['posisi'] : $posisiPengirim;
            $jabatanTujuan = $sipastiUser['jabatan'] ?? 'Administrator';
            $statusRevisi  = 'KMBD';
        } else {
            $posisiTujuan  = $posisiPengirim;
            $jabatanTujuan = 'Administrator';
            $statusRevisi  = 'KMBD';
        }

        $fileRevisiPath = null;
        if ($request->hasFile('file_revisi')) {
            $fileRevisiPath = $request->file('file_revisi')->store('uploads/pengajuan', 'public');
        }

        \App\Models\Log::create([
            'id_pengajuan'   => $id,
            'posisi'         => $posisiTujuan,
            'jabatan'        => $jabatanTujuan ?? 'Administrator',
            'catatan'        => $request->input('catatan', 'Dikembalikan'),
            'tanggal_posisi' => now(),
            'file1'          => $latestLog ? $latestLog->file1 : null,
            'file2'          => $latestLog ? $latestLog->file2 : null,
            'file_revisi'    => $fileRevisiPath ?? '',
            'status'         => $statusRevisi,
        ]);

        // === NOTIFIKASI WA: Surat Dikembalikan ===
        try {
            $pengajuanObj = Pengajuan::with('lembaga')->find($id);
            $ctx = $this->buildWaCtx($pengajuanObj, $posisiTujuan, $request->input('catatan', ''));
            $noHpPengirim = $this->getPengirimNoHp($pengajuanObj);
            $this->kirimWaDikdasmen($noHpPengirim, 'Pemberitahuan Surat Anda Dikembalikan untuk Revisi', $ctx);
        } catch (\Exception $e) { /* Notif WA gagal tidak boleh hentikan proses */ }
        // ========================================================

        return response()->json(['message' => 'Dikembalikan ke ' . $posisiTujuan]);
    }

    public function finish(Request $request, $id)
    {
        Pengajuan::findOrFail($id);
        $latestLog = $this->getLatestLog($id);
        $posisi = $this->resolveUser($request);

        $file1Path = $latestLog ? $latestLog->file1 : null;
        if ($request->input('perubahan_file1') === 'ya' && $request->hasFile('file1')) {
            $file1Path = $request->file('file1')->store('uploads/pengajuan', 'public');
        }

        $file2Path = $latestLog ? $latestLog->file2 : null;
        if ($request->input('perubahan_file2') === 'ya' && $request->hasFile('file2')) {
            $file2Path = $request->file('file2')->store('uploads/pengajuan', 'public');
        }

        $posisiTarget = "Dikdasmen";
        if (strtolower($posisi) === "dikdasmen") {
            $posisiTarget = "Pengirim";
        }

        \App\Models\Log::create([
            "id_pengajuan" => $id,
            "posisi" => $posisiTarget,
            "jabatan" => "Administrator",
            "catatan" => $request->input("catatan", "Selesai"),
            "tanggal_posisi" => now(),
            "file1" => $file1Path,
            "file2" => $file2Path,
            "status" => "FN",
        ]);

        // === NOTIFIKASI WA: Surat Selesai di ACC ===
        try {
            $pengajuanObj = Pengajuan::with('lembaga')->find($id);
            $ctx = $this->buildWaCtx($pengajuanObj, $posisiTarget, $request->input('catatan', ''));
            // Notif ke Admin Dikdasmen (posisiTarget = Dikdasmen)
            $noHpDikdasmen = \App\Models\User::where('name', 'like', '%dikdasmen%')
                ->whereNotNull('no_hp')->value('no_hp');
            $this->kirimWaDikdasmen($noHpDikdasmen, 'Pemberitahuan Surat Telah di ACC', $ctx);
            // Notif ke pengirim awal
            $noHpPengirim = $this->getPengirimNoHp($pengajuanObj);
            $this->kirimWaDikdasmen($noHpPengirim, 'Pemberitahuan Surat Anda Telah di ACC', $ctx);
        } catch (\Exception $e) { /* Notif WA gagal tidak boleh hentikan proses */ }
        // ========================================================

        return response()->json(["message" => "Selesai"]);
    }

    public function log($id)
    {
        $pengajuan = Pengajuan::with("lembaga")->findOrFail($id);
        $logs = \App\Models\Log::where("id_pengajuan", $id)->orderBy("created_at", "desc")->get();
        
        foreach ($logs as $log) {
            $desc = "surat dilanjutkan ke " . $log->posisi . " (" . $log->jabatan . ")";
            if ($log->status == "k") {
                $desc = "surat dilanjutkan ke " . $log->posisi . " (" . $log->jabatan . ")";
            } else if ($log->status == "t") {
                $desc = "surat diterima oleh " . $log->posisi . " (" . $log->jabatan . ")";
            } else if ($log->status == "ACC KABID") {
                $desc = "surat telah ditandatangani kabid dikdasmen";
            } else if ($log->status == "REVISI" || $log->status == "KEMBALIKAN KE STAF") {
                $desc = "surat dikembalikan ke pengirim";
            }
            $log->keterangan_aksi = $desc;
            
            // Format files as absolute URLs for Sitaksi
            if ($log->file1 && !str_starts_with($log->file1, "http")) {
                $log->file1 = "http://localhost/storage/" . $log->file1;
            }
            if ($log->file2 && !str_starts_with($log->file2, "http")) {
                $log->file2 = "http://localhost/storage/" . $log->file2;
            }
            if ($log->file_revisi && !str_starts_with($log->file_revisi, "http")) {
                $log->file_revisi = "http://localhost/storage/" . $log->file_revisi;
            }
        }
        
        return response()->json([
            "pengajuan" => $pengajuan,
            "logs" => $logs,
            "total_durasi" => "-"
        ]);
    }

    /**
     * =====================================================================
     * NOTIFIKASI WA — Lembaga Eksternal (BPK2M/Bendahara/Sekretariat)
     * Mirip 100% dengan Sipasti Pusat.
     * =====================================================================
     */
    private function kirimWaDikdasmen($targetNoHp, string $title, array $ctx = []): void
    {
        if (empty($targetNoHp)) return;

        $pengirim   = $ctx['pengirim']    ?? '-';
        $tujuan     = $ctx['tujuan']      ?? '-';
        $posisi     = $ctx['posisi']      ?? '-';
        $jenis      = $ctx['jenis_surat'] ?? '-';
        $perihal    = $ctx['perihal']     ?? '-';
        $catatan    = $ctx['catatan']     ?? '';
        $waktu      = now()->format('Y-m-d H:i:s');

        $pesan = "*{$title}*\n\n"
               . "Pengirim   : {$pengirim}\n"
               . "Tujuan     : {$tujuan}\n"
               . "Posisi     : *{$posisi}*\n"
               . "Jenis Surat: {$jenis}\n"
               . "Perihal    : {$perihal}\n";

        if (!empty($catatan)) {
            $pesan .= "\n*CATATAN: {$catatan}*\n";
        }

        $pesan .= "Waktu      : {$waktu}\n\n"
               . "Silakan kunjungi sipasti.id untuk menindaklanjuti.\n\n"
               . "Salam,\nHajah Khidmah, ikhtiar menjadi lebih baik";

        SendWaNotification::dispatch($targetNoHp, $pesan);
    }

    private function buildWaCtx(Pengajuan $pengajuan, string $posisi, string $catatan = ''): array
    {
        return [
            'pengirim'   => $pengajuan->lembaga?->nama_lembaga ?? '-',
            'tujuan'     => $pengajuan->tujuan ?? '-',
            'posisi'     => $posisi,
            'jenis_surat'=> $pengajuan->jenis_surat ?? '-',
            'perihal'    => $pengajuan->perihal ?? '-',
            'catatan'    => $catatan,
        ];
    }

    private function getAdminNoHp(string $posisi): ?string
    {
        // Mapping posisi lembaga -> id_lembaga (sesuai lembaga yang ada di DB Sipasti)
        $map = [
            'BPK2M'       => 'LMB0014',
            'Bendahara'   => 'LMB0016',
            'Sekretariat' => 'LMB0015',
        ];
        $idLembaga = $map[$posisi] ?? null;
        if (!$idLembaga) return null;

        // Ambil dari DB sipasti (koneksi sipasti)
        $admin = \Illuminate\Support\Facades\DB::connection('sipasti')
            ->table('user')
            ->where('id_lembaga', $idLembaga)
            ->where('id_jabatan', '1')
            ->whereNotNull('no_hp')
            ->first();

        return $admin?->no_hp ?? null;
    }

    private function getPengirimNoHp(Pengajuan $pengajuan): ?string
    {
        $pengajuan->loadMissing('lembaga');
        $idLembaga = $pengajuan->id_lembaga;
        if (!$idLembaga) return null;

        $pengirim = \App\Models\User::where('id_lembaga', $idLembaga)
            ->where('id_jabatan', '1')
            ->first();
        return $pengirim?->no_hp ?? null;
    }

}