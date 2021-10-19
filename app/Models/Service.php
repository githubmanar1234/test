<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends AppModel
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "services";
    protected $fillable = ['title', 'description', 'subcategory_id', 'user_id', 'location', 'image', 'have_store', 'reject_message', 'status', 'city_id','qualification', 'facebook_link', 'phone_number', 'whatsapp_number','email','parent_id'];
    protected $appends = ['city_name', 'rate'];

//    public function details()
//    {
//        return $this->hasOne(ServiceDetail::class, 'service_id');
//    }

    //protected $with = ['category'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getCityNameAttribute()
    {
        return $this->city()->first()->name;
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function orders()
    {
        return $this->belongsTomany(User::class, 'services_orders', 'service_id', 'user_id')->as('order')->withPivot('status', 'subservices', 'id', 'rate','service_owner_id')->withTimestamps();
    }

    public function getRateAttribute()
    {
        $orders = $this->orders()->whereNotNull('rate')->get();
        if ($orders->count())
            return round($orders->sum('order.rate') / $orders->count());
        return null;
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    public function serviceUpdate()
    {
        return $this->hasOne(Service::class, 'parent_id');
    }
    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function subservices()
    {
        return $this->hasMany(Subservice::class, 'service_id');
    }


    const create_update_rules = [
        'title' => "required",
        'description' => "required",
        'subcategory_id' => "required|exists:categories,id,parent_id,NOT_NULL",
        'user_id' => "required|exists:users,id",
        'qualification' => "nullable",
        'image' => "required|mimes:jpeg,jpg,png|max:2048",
        'have_store' => "required|boolean",
        'location' => ["required", 'regex:/^[-]?((([0-8]?[0-9])(\.(\d{1,8}))?)|(90(\.0+)?)),\s?[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,8}))?)|180(\.0+)?)$/'],
        'facebook_link' => 'url|nullable',
        'email' => 'email|nullable',
        'subservices' => 'required|array',
        'subservices.*' => 'required',
        'subservices.*.name' => 'required',
        'subservices.*.price' => "required|numeric",
//        'subservices.*.service_id' =>  'nullable|exists:services,id',
    ];
}
