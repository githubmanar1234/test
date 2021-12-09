<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Service extends AppModel
{
    use HasFactory,HasTranslations;
    use SoftDeletes;

    protected $table = "services";
    
    protected $fillable = ['title', 'description', 'category_id', 'image'];

    public $translatable = ['title','description'];


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    const create_update_rules = [
        'title' => "required",
        'description' => "required",
        'category_id' => "required|exists:categories,id,NOT_NULL",
        'image' => "mimes:jpeg,jpg,png|max:2048",

    ];
}
