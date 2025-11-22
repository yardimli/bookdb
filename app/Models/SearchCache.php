<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;

	class SearchCache extends Model
	{
		use HasFactory;

		protected $fillable = ['query', 'page', 'results'];

		protected $casts = [
			'results' => 'array', // Automatically convert JSON to Array
		];
	}
