<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Translatable\HasTranslations;

class Barber extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'password',
        'barber_code',
        'salon_id',
        'name',
        'phone_number',
        'facebook_link',
        'instagram_link',
        'whatsapp_number',
        'status',
        'is_availble',
        'city_id',
        'gender',
        'birthday',
        'bio',

        
    ];

    // public $translatable = ['name'];

    protected $with = ['services','timelines','images'];

    protected $appends = ['salonType','rate'];

    protected $hidden = ['phone_number', 'facebook_link','instagram_link','whatsapp_number','password'];

    // public function decryptPassword($password){

    //     //  $password = $data->makeVisible(['password']);
             
    //      Crypt::decrypt($password); 
    //      return $password;
    // }

    //not used yet
    public function images(){

        return $this->hasMany(BarberImage::class,'barber_id');
    }

    public function services(){

        return $this->hasMany(BarberService::class,'barber_id');
    }

    // public function orders(){

    //     return $this->hasMany(Order::class,'barber_id');
    // }

    
    public function getRateAttribute(){

        $orders = Order::where('barber_id',$this->id)->get();

        if(count($orders) > 0){

            return  Order::where('barber_id',$this->id)->avg('rate');
        }
        else{
            return 0;
        }
      
        
    }

    public function timelines(){

        return $this->hasMany(TimingBarber::class,'barber_id');
    }
    
    public function salon()
    {
        return $this->belongsTo(Salon::class, "salon_id", 'id');
    }

    public function getSalonTypeAttribute()
    {
 
         $SalonId = json_decode($this->attributes['salon_id']);
         if ($SalonId) {
 
             if (Salon::find($SalonId)){
               return  Salon::find($SalonId)->type;
             }
             else{
                return "no salon";
             }
           
          }
            
    }

    public function barberReports()
    {
        return $this->hasMany(BarberReport::class);
    }

    // public function country()
    // {
    //     return $this->belongsTo(Country::class, "country_id", 'id');
    // }

    // public function images()
    // {
    //     return $this->morphMany(Image::class, 'imageable');
    // }

    // public function orders()
    // {
    //     return $this->belongsTomany(Service::class, 'services_orders', 'user_id', 'service_id')->as('order')->withPivot('status', 'subservices', 'id', 'rate')->withTimestamps();
    // }

    // public function services()
    // {
    //     return $this->hasMany(Service::class, 'user_id');
    // }

    public function scopeFilter($builder, $filters = null, $filterOperator = "=")
    {
        if (isset($filters) && is_array($filters)) {
            foreach ($filters as $field => $value) {
                if ($value == Constants::NULL)
                    $builder->whereNull($field);
                elseif ($value == Constants::NOT_NULL)
                    $builder->whereNotNull($field);
                elseif (is_array($value))
                    $builder->whereIn($field, $value);
                elseif ($filterOperator == "like")
                    $builder->where($field, $filterOperator, '%' . $value . '%');
                else
                    $builder->where($field, $value);
            }
        }
        return $builder;
    }


    // const create_update_rules = [
    //     'name' => 'required',
    //     'phone' => 'required',
    //     'country_id' => 'required|exists:countries,id',
    //     'fcm_token' => 'required',
    //     'access_token' => 'required',
    //     'lang' => 'required'
    // ];

    // public function getWorkExperienceAttribute()
    // {
    //     return $categoriesIds = json_decode($this->attributes['work_experience']);
    // }

    // public function getWorkExperienceTitlesAttribute()
    // {
    //     $categoriesIds = json_decode($this->attributes['work_experience']);
    //     if ($categoriesIds) return Category::
    //     whereIn('id', $categoriesIds)
    //         ->select('title')->get();
    // }

    // public function setWorkExperienceAttribute($value)
    // {
    //     $this->attributes['work_experience'] = json_encode($value);
    // }

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->toDateTimeString();
    }
}
