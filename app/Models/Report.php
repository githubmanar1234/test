<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends AppModel
{
    use HasFactory;
    protected $fillable = ['reason','reportable_id', 'reportable_type'];

    /**
     * Get the parent imageable model (user or post).
     */
    public function reportable()
    {
        return $this->morphTo();
    }

}
