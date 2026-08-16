<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JenisSuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jenis_surats = \App\Models\JenisSurat::all();
        return view('master.jenis_surat.index', compact('jenis_surats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:100',
        ]);

        \App\Models\JenisSurat::create($request->all());

        return redirect()->route('master.jenis-surat.index')->with('success', 'Data Jenis Surat berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:100',
        ]);

        $jenisSurat = \App\Models\JenisSurat::findOrFail($id);
        $jenisSurat->update($request->only(['nama_jenis']));

        return redirect()->route('master.jenis-surat.index')->with('success', 'Data Jenis Surat berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $jenisSurat = \App\Models\JenisSurat::findOrFail($id);
        $jenisSurat->delete();

        return redirect()->route('master.jenis-surat.index')->with('success', 'Data Jenis Surat berhasil dihapus.');
    }
}
