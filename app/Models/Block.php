<?php

namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Translatable\HasTranslations;

class Block extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = "blockes";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'barber_id',
        'user_id',  
    ];

    


    // const create_update_rules = [
    //     'name' => 'required',
    //     'phone' => 'required',
    //     'country_id' => 'required|exists:countries,id',
    //     'fcm_token' => 'required',
    //     'access_token' => 'required',
    //     'lang' => 'required'
    // ];

    // public function getWorkExperienceAttribute()
    // {
    //     return $categoriesIds = json_decode($this->attributes['work_experience']);
    // }

    // public function getWorkExperienceTitlesAttribute()
    // {
    //     $categoriesIds = json_decode($this->attributes['work_experience']);
    //     if ($categoriesIds) return Category::
    //     whereIn('id', $categoriesIds)
    //         ->select('title')->get();
    // }

    // public function setWorkExperienceAttribute($value)
    // {
    //     $this->attributes['work_experience'] = json_encode($value);
    // }

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->toDateTimeString();
    }
}
