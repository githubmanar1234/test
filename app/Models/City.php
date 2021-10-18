<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends AppModel
{
    use HasFactory;
    protected $table = "cities";
    protected $fillable = ['name','country_id'];

    public function country(){
        return $this->belongsTo(Country::class,'country_id');
    }


}
