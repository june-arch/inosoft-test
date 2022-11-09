<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Kendaraan extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'kendaraans';

    protected $fillable = [
        'kendaraan_id',
        'name',
        'slug',
        'tahun_keluaran',
        'warna',
        'harga',
        'jenis_kendaraan',
        'tipe_suspensi',
        'tipe_transmisi',
        'mesin',
        'kapasitas_penumpang',
        'tipe',
        'stok',
        'status',
        'kode_kendaraan'
    ];
    protected $date = ['created_at','updated_at'];
}
