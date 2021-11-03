<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class OrderService extends Authenticatable
{
    use HasFactory, HasApiTokens;
    
    protected $fillable = ['order_id ', 'service_id'];

   // protected $hidden =['password'];

    
   
    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->toDateTimeString();
    }
}
