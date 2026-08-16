<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    protected $primaryKey = 'id_tahun';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'id_tahun',
        'tahun_akademik',
        'semester',
        'status',
    ];}
