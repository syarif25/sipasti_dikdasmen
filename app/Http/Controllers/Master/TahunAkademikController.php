<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TahunAkademikController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tahun_akademiks = \App\Models\TahunAkademik::all();
        return view('master.tahun_akademik.index', compact('tahun_akademiks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_akademik' => 'required|string|max:20',
            'status' => 'required|string|max:20',
        ]);

        $maxId = \App\Models\TahunAkademik::max('id_tahun') ?? 2024;
        
        $data = $request->all();
        $data['id_tahun'] = $maxId + 1;
        $data['semester'] = '-';

        \App\Models\TahunAkademik::create($data);

        return redirect()->route('master.tahun-akademik.index')->with('success', 'Data Tahun Akademik berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'tahun_akademik' => 'required|string|max:20',
            'status' => 'required|string|max:20',
        ]);

        $tahunAkademik = \App\Models\TahunAkademik::findOrFail($id);
        
        $data = $request->only(['tahun_akademik', 'status']);
        $data['semester'] = '-';
        
        $tahunAkademik->update($data);

        return redirect()->route('master.tahun-akademik.index')->with('success', 'Data Tahun Akademik berhasil diperbarui.');
    }

    public function activate(string $id)
    {
        // Set all to Tidak Aktif
        \App\Models\TahunAkademik::where('status', 'Aktif')->update(['status' => 'Tidak Aktif']);
        
        // Set selected to Aktif
        $tahunAkademik = \App\Models\TahunAkademik::findOrFail($id);
        $tahunAkademik->update(['status' => 'Aktif']);

        return redirect()->route('master.tahun-akademik.index')->with('success', 'Tahun Akademik ' . $tahunAkademik->tahun_akademik . ' berhasil diaktifkan.');
    }

    public function destroy(string $id)
    {
        $tahunAkademik = \App\Models\TahunAkademik::findOrFail($id);
        $tahunAkademik->delete();

        return redirect()->route('master.tahun-akademik.index')->with('success', 'Data Tahun Akademik berhasil dihapus.');
    }
}
