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
    protected $fillable = ['name', 'status', 'salon_code', 'city_id' , 'user_id'];
    public $translatable = ['name'];


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
