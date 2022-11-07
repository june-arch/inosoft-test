<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Kendaraan extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'kendaraans';

    protected $fillable = ['kendaraan_id','tahun_keluaran','warna','harga','id_jenis_kendaraan'];
    protected $date = ['created_at','updated_at'];
}
