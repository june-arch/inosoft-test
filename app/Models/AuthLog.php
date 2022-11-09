<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AuthLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'auth_logs';

    protected $fillable = ['auth_log_id','user'];
    protected $date = ['created_at','updated_at'];
}
