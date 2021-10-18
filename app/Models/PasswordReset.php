<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PasswordReset extends AppModel
{
    use HasFactory;
    protected $fillable = ['reset_code', 'email'];
    public $timestamps = false;


}
