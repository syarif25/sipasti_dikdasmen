<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lembaga;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $penggunas = User::with(['lembaga', 'jabatan'])->get();
        $lembagas = Lembaga::all();
        $jabatans = Jabatan::all();
        return view('pengguna.index', compact('penggunas', 'lembagas', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'no_hp' => 'nullable|string|max:20',
            'level' => 'required|integer',
            'status' => 'required|integer',
        ]);

        $data = $request->except(['password']);
        $data['password'] = Hash::make($request->password);

        // Dynamic relations
        if ($request->level == 1) {
            $data['id_lembaga'] = $request->id_lembaga;
            $data['id_jabatan'] = null;
        } elseif ($request->level >= 2 && $request->level <= 6) {
            $data['id_jabatan'] = $request->id_jabatan;
            $data['id_lembaga'] = null;
        } else {
            $data['id_lembaga'] = null;
            $data['id_jabatan'] = null;
        }

        User::create($data);

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6',
            'no_hp' => 'nullable|string|max:20',
            'level' => 'required|integer',
            'status' => 'required|integer',
        ]);

        $data = $request->except(['password']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Dynamic relations
        if ($request->level == 1) {
            $data['id_lembaga'] = $request->id_lembaga;
            $data['id_jabatan'] = null;
        } elseif ($request->level >= 2 && $request->level <= 6) {
            $data['id_jabatan'] = $request->id_jabatan;
            $data['id_lembaga'] = null;
        } else {
            $data['id_lembaga'] = null;
            $data['id_jabatan'] = null;
        }

        $user->update($data);

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if (auth()->id() == $user->id_user) {
            return redirect()->route('pengguna.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }
        
        $user->delete();

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil dihapus!');
    }
}
