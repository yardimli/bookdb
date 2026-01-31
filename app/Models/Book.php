<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\UserBookStatus;
use App\Models\UserBookReadLog;

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

    public function userStatuses()
    {
        return $this->hasMany(UserBookStatus::class);
    }

    public function readLogs()
    {
        return $this->hasMany(UserBookReadLog::class);
    }

}
