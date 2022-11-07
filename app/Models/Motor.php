<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Motor extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'motors';

    protected $fillable = ['jenis_kendaraan_id','mesin','tipe_suspensi','tipe_transmisi'];
    protected $date = ['created_at','updated_at'];
}
