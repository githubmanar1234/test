<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BarberImage extends AppModel
{
    use HasFactory,HasTranslations;
    //use SoftDeletes;

    
    protected $fillable = ['barber_id', 'image'];

    
    protected $hidden = ['id', 'barber_id', 'deleted_at' , 'updated_at'];

   
    public function getImageAttribute()
    {
        return url($this->attributes['image']) ;
    }

}
