<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends AppModel
{
    use HasFactory;
    protected $appends = ['label'];
    protected $fillable = ["key", "value", "default", 'description'];

    public function getLabelAttribute()
    {
        return Helpers::underScoreToWords($this->key);
    }

    const create_update_rules = [
        "key" => "required",
        "value" => "required"
    ];
}
