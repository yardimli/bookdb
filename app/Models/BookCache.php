<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCache extends Model
{
	protected $fillable = ['goodreads_id', 'data'];

	protected $casts = [
		'data' => 'array', // Automatically convert JSON to Array
	];
}
