<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'posisi',
        'jabatan',
        'catatan',
        'file1',
        'file2',
        'file_revisi',
        'status',
    ];}
