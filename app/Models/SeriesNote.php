<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesNote extends Model
{
    protected $fillable = [
        'series_id',
        'content',
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
