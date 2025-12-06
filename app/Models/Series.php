<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

	protected $fillable = ['user_id', 'title', 'description', 'books'];

	public function books()
	{
    	return $this->belongsToMany(Book::class, 'series_book')
			->withTimestamps()
            ->withPivot('sort_order')
            ->orderBy('series_book.sort_order');
	}


}
