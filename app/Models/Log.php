<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_pengajuan',
        'posisi',
        'jabatan',
        'catatan',
        'catatanurgen',
        'tanggal_posisi',
        'file1',
        'file2',
        'file_revisi',
        'status',
    ];}
