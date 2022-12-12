<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model{
	use HasFactory;

	public $timestamps = false;

	public function getImageAttribute($val){
		return Storage::disk('s3')->url($val);
	}
}
