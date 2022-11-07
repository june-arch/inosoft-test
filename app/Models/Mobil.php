<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Mobil extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'mobils';

    protected $fillable = ['jenis_kendaraan_id','mesin','kapasitas_penumpang','tipe'];
    protected $date = ['created_at','updated_at'];
}
