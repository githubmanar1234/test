<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class Category extends AppModel
{
    use HasFactory, HasTranslations;

    protected $table = "categories";

    protected $fillable = ['title','order','description'];
    public $translatable = ['title','description'];

    public function services(){
        return $this->hasMany(Service::class,'category_id');
    }

}
