<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LembagaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lembagas = \App\Models\Lembaga::all();
        return view('master.lembaga.index', compact('lembagas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lembaga' => 'required|string|max:50',
            'singkatan_lembaga' => 'required|string|max:15',
        ]);

        $lastLembaga = \App\Models\Lembaga::orderBy('id_lembaga', 'desc')->first();
        $nextIdNumber = 1;
        if ($lastLembaga) {
            $lastIdStr = substr($lastLembaga->id_lembaga, 3);
            $nextIdNumber = intval($lastIdStr) + 1;
        }
        $newIdLembaga = 'LMB' . str_pad($nextIdNumber, 3, '0', STR_PAD_LEFT);

        \App\Models\Lembaga::create([
            'id_lembaga' => $newIdLembaga,
            'nama_lembaga' => $request->nama_lembaga,
            'singkatan_lembaga' => $request->singkatan_lembaga,
        ]);

        return redirect()->route('master.lembaga.index')->with('success', 'Data Lembaga berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_lembaga' => 'required|string|max:50',
            'singkatan_lembaga' => 'required|string|max:15',
        ]);

        $lembaga = \App\Models\Lembaga::findOrFail($id);
        $lembaga->update($request->only(['nama_lembaga', 'singkatan_lembaga']));

        return redirect()->route('master.lembaga.index')->with('success', 'Data Lembaga berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $lembaga = \App\Models\Lembaga::findOrFail($id);
        $lembaga->delete();

        return redirect()->route('master.lembaga.index')->with('success', 'Data Lembaga berhasil dihapus.');
    }
}
