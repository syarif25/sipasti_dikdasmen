<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Lembaga;
use App\Models\JenisSurat;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class ManajemenPengajuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pengajuans = Pengajuan::with(['lembaga'])->get();
        $lembagas = Lembaga::all();
        $jenisSurats = JenisSurat::all();
        $tahunAkademiks = TahunAkademik::all();
        return view('manajemen-pengajuan.index', compact('pengajuans', 'lembagas', 'jenisSurats', 'tahunAkademiks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // CRUD Store if needed, but usually admin just edits or deletes. Let's support update and delete.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:100',
            'perihal' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'jenis_surat' => 'required|string|max:255',
            'id_lembaga' => 'required|string|max:10',
            'id_tahun' => 'required|integer',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->update([
            'nomor_surat' => $request->nomor_surat,
            'perihal' => $request->perihal,
            'tujuan' => $request->tujuan,
            'jenis_surat' => $request->jenis_surat,
            'id_lembaga' => $request->id_lembaga,
            'id_tahun' => $request->id_tahun,
        ]);

        return redirect()->back()->with('success', 'Data pengajuan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->delete();

        return redirect()->back()->with('success', 'Data pengajuan berhasil dihapus!');
    }
}
