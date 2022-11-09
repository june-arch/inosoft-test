<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class PaymentProduct extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'payment_products';

    protected $date = ['created_at','updated_at'];
}
