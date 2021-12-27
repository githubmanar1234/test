<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Salon extends AppModel
{
    use HasFactory;
    //use SoftDeletes;

    protected $table = "salons";


    protected $fillable = ['name', 'status', 'description','bio','reason','salon_code','berbers_num', 'city_id' 
    , 'type' ,'image', 'is_open','address','lat_location','long_location','phone_number',
    'facebook_link','whatsapp_number','is_available','founded_in','instagram_link'];

    // public $translatable = ['name'];

    protected $hidden =['city_id'];

    protected $with = ['barbers' ,'timings'];
    
    protected $appends = ['owner' , 'city' ,'country','rate'];

    public function barbers(){
        
        return $this->hasMany(Barber::class,'salon_id');
    }

       
    public function getRateAttribute(){

        $barbers = $this->barbers;
        
        if(count($barbers) > 0){

            $rateBarbers = 0;
            $countRateBarbers = 0;

            foreach($barbers as $barber){

                if($barber->getRateAttribute()){
                    $rateBarbers += $barber->getRateAttribute();
                    $countRateBarbers = $countRateBarbers + 1;
                }
               
            }
             return $rateBarbers / ($countRateBarbers);
            
        }
        else{
            return 0;
        }
      
        
    }
    public function timings(){
        
        return $this->hasMany(Timing::class,'salon_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, "city_id", 'id');
    }

    public function getImageAttribute()
    {
        return url($this->attributes['image']) ;
    }

    // public function users()
    // {
    //     $users = User::whereHas('salonReport', function ($q)  {
    //         $q->where('salon_id', $this->id );
    //     })->orderBy('id', 'desc')->get();

    //     return $users;
    // }

    public function salonReports()
    {
        return $this->hasMany(SalonReport::class);
    }
    
    //not used yet
    // public function user()
    // {
    //     return 'sa';
    // }

    public function getOwnerAttribute()
    {
        $isExist = User::where('salon_id',$this->id)->first();
        if ($isExist){
            return $isExist->name;
        }

        return "no Owner";
    }

    public function getCityAttribute()
    {
 

        $cityId = json_decode($this->attributes['city_id']);
        if ($cityId) {

           if (City::find($cityId)){
               return City::find($cityId)->name;
           }
           else{
               return "no City";

           }
   
         }
            
    }

    public function getCountryAttribute()
    {

        $cityId = json_decode($this->attributes['city_id']);
        if ($cityId) {

           if (City::find($cityId)){
               if(City::find($cityId)->country){
                    return City::find($cityId)->country->name;
               }
               else{
                    return "no country";
               }
           }
           else{
               return "no City";
           }
        }
            
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class, 'category_id');
    // }


    // const create_update_rules = [
    //     'title' => "required",
    //     'description' => "required",
    //     'category_id' => "required|exists:categories,id,NOT_NULL",
    //     'image' => "mimes:jpeg,jpg,png|max:2048",

    // ];
}
