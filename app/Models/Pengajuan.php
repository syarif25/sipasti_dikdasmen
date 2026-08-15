<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $fillable = [
        'perihal',
        'jenis_surat',
        'ket',
        'id_lembaga',
        'user_id',
    ];}
