<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dict extends Model
{
    use HasFactory;

    protected $casts = [
        'dict_val_json' => 'json',
    ];

}
