<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimingBarber extends AppModel
{
    use HasFactory;
    
    protected $fillable = ['day','from','to', 'barber_id'];

    
    protected $hidden = ['barber_id'];

    // public function users(){
    //     return $this->hasMany(User::class,'country_id');
    // }
    // public function cities(){
    //     return $this->hasMany(City::class,'country_id');
    // }

}
