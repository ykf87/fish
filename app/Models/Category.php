<?php

namespace App\Models;

use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use ModelTree;

    protected $table = 'categories';
}
