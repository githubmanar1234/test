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

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'whatsapp_number',
        'profile_image',
        'email',
        'firebase_uid',
        'yob',
        'country_id',
        'fcm_token',
        'skills',
        'lang',
        'work_experience',
        'location'
    ];

    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $with = ['country'];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'yob' => 'datetime:Y-m-d',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, "country_id", 'id');
    }

    // salon id
    public function salon()
    {
        return $this->belongsTo(Salon::class, 'salon_id', 'id');
    }

    // public function images()
    // {
    //     return $this->morphMany(Image::class, 'imageable');
    // }

    public function orders()
    {
        return $this->belongsTomany(Service::class, 'services_orders', 'user_id', 'service_id')->as('order')->withPivot('status', 'subservices', 'id', 'rate')->withTimestamps();
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'user_id');
    }

    public function scopeFilter($builder, $filters = null, $filterOperator = "=")
    {
        if (isset($filters) && is_array($filters)) {
            foreach ($filters as $field => $value) {
                if ($value == Constants::NULL)
                    $builder->whereNull($field);
                elseif ($value == Constants::NOT_NULL)
                    $builder->whereNotNull($field);
                elseif (is_array($value))
                    $builder->whereIn($field, $value);
                elseif ($filterOperator == "like")
                    $builder->where($field, $filterOperator, '%' . $value . '%');
                else
                    $builder->where($field, $value);
            }
        }
        return $builder;
    }


    const create_update_rules = [
        'name' => 'required',
        'phone' => 'required',
        'country_id' => 'required|exists:countries,id',
        'fcm_token' => 'required',
        'access_token' => 'required',
        'lang' => 'required'
    ];

    public function getProfileImageAttribute()
    {
        return url($this->attributes['profile_image']) ;
    }

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
