<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

	protected $fillable = ['user_id', 'title', 'description', 'books'];

	protected $casts = [
		'books' => 'array', // Automatically convert JSON to Array
	];

}
