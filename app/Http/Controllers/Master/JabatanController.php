<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jabatans = \App\Models\Jabatan::all();
        return view('master.jabatan.index', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_jabatan' => 'required|integer|unique:jabatans',
            'nama_jabatan' => 'required|string|max:50',
        ]);

        \App\Models\Jabatan::create($request->all());

        return redirect()->route('master.jabatan.index')->with('success', 'Data Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:50',
        ]);

        $jabatan = \App\Models\Jabatan::findOrFail($id);
        $jabatan->update($request->only(['nama_jabatan']));

        return redirect()->route('master.jabatan.index')->with('success', 'Data Jabatan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $jabatan = \App\Models\Jabatan::findOrFail($id);
        $jabatan->delete();

        return redirect()->route('master.jabatan.index')->with('success', 'Data Jabatan berhasil dihapus.');
    }
}
