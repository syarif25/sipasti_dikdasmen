<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $primaryKey = 'id_pengajuan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pengajuan',
        'nomor_surat',
        'perihal',
        'tujuan',
        'jenis_surat',
        'ket',
        'tgl_upload',
        'id_tahun',
        'pencairan',
        'lpj',
        'id_lembaga',
        'user_id',
    ];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class, 'id_lembaga', 'id_lembaga');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'id_pengajuan', 'id_pengajuan')->orderBy('created_at', 'asc');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_tahun', 'id_tahun');
    }
}
