<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Invoice extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'invoices';

    protected $fillable = ['invoice_id','user','kendaraan','metode_pembayaran','status','total_price'];
    protected $date = ['created_at','updated_at'];
}
