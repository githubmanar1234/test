<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Post extends AppModel
{
    use HasFactory,HasTranslations;
    //use SoftDeletes;

    protected $fillable = ['description', 'salon_id' , 'image','published_at'];
    // public $translatable = ['description'];

    protected $appends = ['salon' , 'countLikes'];

    public function postReports()
    {
        return $this->hasMany(PostReport::class);
    }

    public function getSalonAttribute()
    {
        $salon = Salon::where('id',$this->salon_id)->first();
        if ($salon){

            return ['name' => $salon->name ,'description' =>  $salon->description ,'profile' =>$salon->image];
        }

        return "No salon";
    }

    public function getCountLikesAttribute()
    {
        $countLikes = PostLike::where('post_id',$this->id)->count();
        
        return $countLikes;
        

        
    }

    
    public function salon()
    {
        return $this->belongsTo(Salon::class, "salon_id", 'id');
    }

  

    // public function users()
    // {
    //     $users = User::whereHas('salonReport', function ($q)  {
    //         $q->where('salon_id', $this->id );
    //     })->orderBy('id', 'desc')->get();

    //     return $users;
    // }

    // public function salonReport()
    // {
    //     return $this->hasMany(SalonReport::class);
    // }

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
