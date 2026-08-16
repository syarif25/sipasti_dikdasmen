<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $primaryKey = 'id_jabatan';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'id_jabatan',
        'nama_jabatan',
    ];}
