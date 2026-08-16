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
    ];}
