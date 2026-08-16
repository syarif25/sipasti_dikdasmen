<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembaga extends Model
{
    protected $primaryKey = 'id_lembaga';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_lembaga',
        'nama_lembaga',
        'singkatan_lembaga',
    ];}
