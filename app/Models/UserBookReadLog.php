<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBookReadLog extends Model
{
    protected $fillable = [
        'user_id','book_id','started_at','finished_at','note'
    ];

    protected $casts = [
        'started_at' => 'date',
        'finished_at' => 'date',
    ];
}
