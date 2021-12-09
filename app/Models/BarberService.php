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

   protected $hidden =['service_id'];
   
//    $serviceTitle = Service::find($service_id)->title ;

   protected $appends = ['service'];

   public $translatable = ['service'];

   public function getServiceAttribute()
   {

        $ServiceId = json_decode($this->attributes['service_id']);
        if ($ServiceId) {

            if (Service::find($ServiceId)){
                $service = Service::find($ServiceId);
                $title["en"] = $service->getTranslation("title", "en");
                $title["ar"] = $service->getTranslation("title", "ar");
                $title["tr"] = $service->getTranslation("title", "tr");
    
            }
            else{
                $title = "";
            }
          
            return $title;
         }
           
   }


   public function services()
   {
       return $this->belongsTo(Service::class, "service_id", 'id');
   }

   

    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->toDateTimeString();
    }
}
