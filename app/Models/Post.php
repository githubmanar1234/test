<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Post extends AppModel
{
    use HasFactory,HasTranslations;
    //use SoftDeletes;

    protected $fillable = ['description', 'salon_id' , 'image'];
    public $translatable = ['description'];



    public function postReports()
    {
        return $this->hasMany(PostReport::class);
    }

  

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
