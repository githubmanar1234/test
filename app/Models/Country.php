<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends AppModel
{
    use HasFactory;
    protected $table = "countries";
    protected $fillable = ['name','flag', 'code','dial_code'];

    public function users(){
        return $this->hasMany(User::class,'country_id');
    }
    public function cities(){
        return $this->hasMany(City::class,'country_id');
    }

}
