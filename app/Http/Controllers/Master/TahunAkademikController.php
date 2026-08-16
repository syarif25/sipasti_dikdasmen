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
            'id_tahun' => 'required|integer|unique:tahun_akademiks',
            'tahun_akademik' => 'required|string|max:20',
            'semester' => 'required|string|max:20',
            'status' => 'required|string|max:20',
        ]);

        \App\Models\TahunAkademik::create($request->all());

        return redirect()->route('master.tahun-akademik.index')->with('success', 'Data Tahun Akademik berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'tahun_akademik' => 'required|string|max:20',
            'semester' => 'required|string|max:20',
            'status' => 'required|string|max:20',
        ]);

        $tahunAkademik = \App\Models\TahunAkademik::findOrFail($id);
        $tahunAkademik->update($request->only(['tahun_akademik', 'semester', 'status']));

        return redirect()->route('master.tahun-akademik.index')->with('success', 'Data Tahun Akademik berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $tahunAkademik = \App\Models\TahunAkademik::findOrFail($id);
        $tahunAkademik->delete();

        return redirect()->route('master.tahun-akademik.index')->with('success', 'Data Tahun Akademik berhasil dihapus.');
    }
}
