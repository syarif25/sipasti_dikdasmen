<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Pengajuan;
use Illuminate\Http\Request;

class ManajemenLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Load logs with their related pengajuan, latest first
        $logs = Log::with('pengajuan')->orderBy('created_at', 'desc')->get();
        $pengajuans = Pengajuan::orderBy('id_pengajuan', 'desc')->get(); // For dropdown

        return view('manajemen-log.index', compact('logs', 'pengajuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pengajuan' => 'required|exists:pengajuans,id_pengajuan',
            'posisi' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'tanggal_posisi' => 'required|date',
            'catatan' => 'nullable|string',
            'file1' => 'nullable|mimes:pdf|max:10240',
            'file2' => 'nullable|mimes:pdf|max:10240',
        ]);

        $data = $request->except(['file1', 'file2']);

        if ($request->hasFile('file1')) {
            $data['file1'] = $request->file('file1')->store('pengajuan', 'public');
        }
        if ($request->hasFile('file2')) {
            $data['file2'] = $request->file('file2')->store('pengajuan', 'public');
        }

        Log::create($data);

        return redirect()->route('manajemen-log.index')->with('success', 'Log berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'id_pengajuan' => 'required|exists:pengajuans,id_pengajuan',
            'posisi' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'tanggal_posisi' => 'required|date',
            'catatan' => 'nullable|string',
            'file1' => 'nullable|mimes:pdf|max:10240',
            'file2' => 'nullable|mimes:pdf|max:10240',
        ]);

        $log = Log::findOrFail($id);
        $data = $request->except(['file1', 'file2']);

        if ($request->hasFile('file1')) {
            $data['file1'] = $request->file('file1')->store('pengajuan', 'public');
        }
        if ($request->hasFile('file2')) {
            $data['file2'] = $request->file('file2')->store('pengajuan', 'public');
        }

        $log->update($data);

        return redirect()->route('manajemen-log.index')->with('success', 'Log berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $log = Log::findOrFail($id);
        $log->delete();

        return redirect()->route('manajemen-log.index')->with('success', 'Log berhasil dihapus.');
    }
}
