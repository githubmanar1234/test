<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Order extends AppModel
{
    use HasFactory,HasTranslations;
    //use SoftDeletes;

    protected $fillable = ['date', 'start_time' , 'end_time' , 'order_number' , 'status', 'user_id', 'barber_id' , 
    'rate', 'reject_message' ,'notes'];

     protected $hidden = ['updatedAt', 'createdAt','deletedAt' ];

     protected $with = ['services'];

     public function services(){

        return $this->hasMany(OrderService::class,'order_id');
    }

    //public $translatable = ['reason'];

    // protected $with = ['user'];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, "user_id", 'id');
    // }
    

    // public function users()
    // {
    //     $users = User::whereHas('salonReport', function ($q)  {
    //         $q->where('salon_id', $this->id );
    //     })->orderBy('id', 'desc')->get();

    //     return $users;
    // }

    // public function salonReport()
    // {
    //     return $this->hasMany(SalonReport::class);
    // }

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
