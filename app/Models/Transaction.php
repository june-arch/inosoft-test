<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Transaction extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'transactions';

    protected $fillable = ['transaction_id','inv_number','user','kendaraan','payment_method','expired','status','total_price'];
    protected $date = ['created_at','updated_at'];
}
