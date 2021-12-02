<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class BarberService extends Authenticatable
{
    use HasFactory, HasApiTokens;
    
    protected $fillable = ['price', 'duration', 'barber_id' , 'service_id'];
   // protected $hidden =['password'];

   //protected $with = ['services'];

   public function services()
   {
       return $this->belongsTo(Service::class, "service_id", 'id');
   }

   public function getServiceIdAttribute()
   {

        $ServiceId = json_decode($this->attributes['service_id']);
        if ($ServiceId) {
             return Service::find($ServiceId)->title;
               
         }
           
   }

    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->toDateTimeString();
    }
}
