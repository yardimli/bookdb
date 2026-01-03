<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Series;

class Genre extends Model
{
    protected $fillable = ['name'];

    public function series()
    {
        return $this->belongsToMany(Series::class)
                    ->withTimestamps();
    }
}
