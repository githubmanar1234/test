<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class OrderService extends Authenticatable
{
    use HasFactory, HasApiTokens;
    
    protected $fillable = ['order_id ', 'bareber_services_id '];

   // protected $hidden =['password'];

   protected $with = ['barberService'];

   public function barberService()
    {
        return $this->belongsTo(BarberService::class, "bareber_services_id", 'id');
    }

    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->toDateTimeString();
    }
}
