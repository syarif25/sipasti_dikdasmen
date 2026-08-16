<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Lembaga::create([
            'id_lembaga' => 'LMB001',
            'nama_lembaga' => 'SD Negeri 01',
            'singkatan_lembaga' => 'SDN 01',
        ]);
        \App\Models\Lembaga::create([
            'id_lembaga' => 'LMB002',
            'nama_lembaga' => 'SMP Negeri 02',
            'singkatan_lembaga' => 'SMPN 02',
        ]);

        \App\Models\Jabatan::create([
            'id_jabatan' => 1,
            'nama_jabatan' => 'Kepala Sekolah',
        ]);
        \App\Models\Jabatan::create([
            'id_jabatan' => 2,
            'nama_jabatan' => 'Guru',
        ]);
        \App\Models\Jabatan::create([
            'id_jabatan' => 3,
            'nama_jabatan' => 'Staf Bidang',
        ]);

        \App\Models\JenisSurat::create([
            'nama_jenis' => 'Pencairan BOS',
        ]);
        \App\Models\JenisSurat::create([
            'nama_jenis' => 'Rekomendasi Mutasi',
        ]);
        \App\Models\JenisSurat::create([
            'nama_jenis' => 'Izin Operasional',
        ]);

        \App\Models\TahunAkademik::create([
            'id_tahun' => 2025,
            'tahun_akademik' => '2025/2026',
            'semester' => 'Ganjil',
            'status' => 'Aktif',
        ]);
        \App\Models\TahunAkademik::create([
            'id_tahun' => 2026,
            'tahun_akademik' => '2025/2026',
            'semester' => 'Genap',
            'status' => 'Tidak Aktif',
        ]);    }
}
