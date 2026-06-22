<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist',
        'duration',
        'track_url',
        'cover_url',
    ];

    public function queueItems()
    {
        return $this->hasMany(QueueItem::class);
    }
}
