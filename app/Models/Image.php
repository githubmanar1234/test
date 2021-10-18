<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends AppModel
{
    use HasFactory;
    protected $fillable = ['path','iamgeable_id', 'imageable_type'];

    /**
     * Get the parent imageable model (user or post).
     */
    public function imageable()
    {
        return $this->morphTo();
    }

}
