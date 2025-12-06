<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'author', 'cover', 'rating', 'external_id'
    ];

    public function series()
    {
        return $this->belongsToMany(Series::class, 'series_book')
                    ->withTimestamps()
                    ->withPivot('sort_order')
                    ->orderBy('series_book.sort_order');
    }
}
