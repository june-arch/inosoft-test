<?php

namespace App\Models;

use Jenssegers\Mongodb\Auth\User as Authenticable;

class User extends Authenticable
{
    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = ['user_id','fullname','email','no_hp','password'];
    protected $date = ['created_at','updated_at'];
}
