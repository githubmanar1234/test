<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PostLike extends AppModel
{
    use HasFactory,HasTranslations;
    //use SoftDeletes;

    
    protected $fillable = ['post_id', 'user_id'];

    

   
}
