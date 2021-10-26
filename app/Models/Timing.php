<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timing extends AppModel
{
    use HasFactory;
    
    protected $fillable = ['day','from','to', 'salon_id'];

    // public function users(){
    //     return $this->hasMany(User::class,'country_id');
    // }
    // public function cities(){
    //     return $this->hasMany(City::class,'country_id');
    // }

}
