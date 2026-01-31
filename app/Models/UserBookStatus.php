<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBookStatus extends Model
{
    protected $fillable = [
        'user_id','book_id','status',
        'progress_page','progress_percent',
        'started_at','finished_at','dnf_at'
    ];

    protected $casts = [
        'started_at' => 'date',
        'finished_at' => 'date',
        'dnf_at' => 'date',
    ];
}
