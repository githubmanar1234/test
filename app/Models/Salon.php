<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Salon extends AppModel
{
    use HasFactory,HasTranslations;
    //use SoftDeletes;

    protected $table = "salons";


    protected $fillable = ['name', 'status', 'description','bio','reason','salon_code','berbers_num', 'city_id' 
    , 'type' ,'image', 'is_open','location','lat_location','long_location','phone_number',
    'facebook_link','whatsapp_number','is_available'];

    public $translatable = ['name'];

    protected $with = ['barbers' ,'timings'];
    
    protected $appends = ['owner'];

    public function barbers(){
        
        return $this->hasMany(Barber::class,'salon_id');
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
        return User::where('salon_id',$this->id)->first()->name;
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
