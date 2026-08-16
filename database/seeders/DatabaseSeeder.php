<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
        ]);
        // User::factory(10)->create();
        
        // Ensure Jabatans exist in DB or update them
        \App\Models\Jabatan::firstOrCreate(['id_jabatan' => 1], ['nama_jabatan' => 'Kepala Sekolah']);
        \App\Models\Jabatan::firstOrCreate(['id_jabatan' => 2], ['nama_jabatan' => 'Staf Bidang']);
        \App\Models\Jabatan::firstOrCreate(['id_jabatan' => 3], ['nama_jabatan' => 'Kasubag']);
        \App\Models\Jabatan::firstOrCreate(['id_jabatan' => 4], ['nama_jabatan' => 'Kabag']);
        \App\Models\Jabatan::firstOrCreate(['id_jabatan' => 5], ['nama_jabatan' => 'KTU']);
        \App\Models\Jabatan::firstOrCreate(['id_jabatan' => 6], ['nama_jabatan' => 'Kabid']);
        \App\Models\Jabatan::firstOrCreate(['id_jabatan' => 7], ['nama_jabatan' => 'Super Admin']);

        $userSekolah = \App\Models\User::factory()->create([
            'name' => 'Admin Sekolah',
            'username' => 'sekolah',
            'email' => 'sekolah@example.com',
            'password' => bcrypt('password'),
            'level' => '1',
            'id_lembaga' => 'LMB001',
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Staf Bidang',
            'username' => 'staf',
            'email' => 'staf@example.com',
            'password' => bcrypt('password'),
            'level' => '2',
            'id_jabatan' => 2,
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kasubag',
            'username' => 'kasubag',
            'email' => 'kasubag@example.com',
            'password' => bcrypt('password'),
            'level' => '3',
            'id_jabatan' => 3,
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kabag',
            'username' => 'kabag',
            'email' => 'kabag@example.com',
            'password' => bcrypt('password'),
            'level' => '4',
            'id_jabatan' => 4,
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'KTU',
            'username' => 'ktu',
            'email' => 'ktu@example.com',
            'password' => bcrypt('password'),
            'level' => '5',
            'id_jabatan' => 5,
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Kabid',
            'username' => 'kabid',
            'email' => 'kabid@example.com',
            'password' => bcrypt('password'),
            'level' => '6',
            'id_jabatan' => 6,
            'status' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'level' => '7',
            'id_jabatan' => 7,
            'status' => 1,
        ]);

        // Seed Dummy Pengajuans
        $p1 = \App\Models\Pengajuan::create([
            'id_pengajuan' => 'PGJ' . time() . '1',
            'nomor_surat' => '101/SDN01/VIII/2025',
            'perihal' => 'Rekomendasi Pencairan Dana Jasket PBSB',
            'tujuan' => 'Staf Bidang',
            'jenis_surat' => 'Pencairan BOS',
            'ket' => 'Mohon segera diproses.',
            'tgl_upload' => now()->subDays(2),
            'id_tahun' => 2025,
            'pencairan' => '-',
            'lpj' => 0,
            'id_lembaga' => 'LMB001',
            'user_id' => $userSekolah->id_user,
        ]);

        \App\Models\Log::create([
            'id_pengajuan' => $p1->id_pengajuan,
            'posisi' => 'SD Negeri 01',
            'jabatan' => 'Pengunggah',
            'catatan' => 'Pengajuan awal diunggah.',
            'tanggal_posisi' => now()->subDays(2),
            'status' => 'DALAM PROSES',
        ]);

        $p2 = \App\Models\Pengajuan::create([
            'id_pengajuan' => 'PGJ' . time() . '2',
            'nomor_surat' => '102/SDN01/VIII/2025',
            'perihal' => 'Evaluasi PBSB Jakarta',
            'tujuan' => 'Kasubag',
            'jenis_surat' => 'Rekomendasi Mutasi',
            'ket' => 'Laporan evaluasi lengkap.',
            'tgl_upload' => now()->subDay(),
            'id_tahun' => 2025,
            'pencairan' => '-',
            'lpj' => 0,
            'id_lembaga' => 'LMB001',
            'user_id' => $userSekolah->id_user,
        ]);

        \App\Models\Log::create([
            'id_pengajuan' => $p2->id_pengajuan,
            'posisi' => 'SD Negeri 01',
            'jabatan' => 'Pengunggah',
            'catatan' => 'Pengajuan awal diunggah.',
            'tanggal_posisi' => now()->subDay(),
            'status' => 'DALAM PROSES',
        ]);
        
        \App\Models\Log::create([
            'id_pengajuan' => $p2->id_pengajuan,
            'posisi' => 'DIKTI',
            'jabatan' => 'Staf Bidang',
            'catatan' => 'Dokumen diteruskan oleh Sekolah.',
            'tanggal_posisi' => now()->subHours(12),
            'status' => 'DALAM PROSES',
        ]);

        // A final/archived submission
        $p3 = \App\Models\Pengajuan::create([
            'id_pengajuan' => 'PGJ' . time() . '3',
            'nomor_surat' => '103/SDN01/VIII/2025',
            'perihal' => 'Pengajuan Izin Operasional Lab Komputer',
            'tujuan' => 'Kabid',
            'jenis_surat' => 'Izin Operasional',
            'ket' => 'Semua syarat sudah dipenuhi.',
            'tgl_upload' => now()->subDays(5),
            'id_tahun' => 2025,
            'pencairan' => '-',
            'lpj' => 0,
            'id_lembaga' => 'LMB001',
            'user_id' => $userSekolah->id_user,
        ]);

        \App\Models\Log::create([
            'id_pengajuan' => $p3->id_pengajuan,
            'posisi' => 'SD Negeri 01',
            'jabatan' => 'Pengunggah',
            'catatan' => 'Pengajuan awal diunggah.',
            'tanggal_posisi' => now()->subDays(5),
            'status' => 'DALAM PROSES',
        ]);

        \App\Models\Log::create([
            'id_pengajuan' => $p3->id_pengajuan,
            'posisi' => 'DIKTI',
            'jabatan' => 'Kabid',
            'catatan' => 'Dokumen disetujui dan ditandai Selesai.',
            'tanggal_posisi' => now()->subDays(4),
            'status' => 'SELESAI',
        ]);
    }
}
