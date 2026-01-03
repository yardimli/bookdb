<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\SeriesNote;
use App\Models\Genre;

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

	public function notes()
	{
    	return $this->hasMany(SeriesNote::class);
	}

	public function genres()
    {
        return $this->belongsToMany(Genre::class)
                    ->withTimestamps();
    }

}
