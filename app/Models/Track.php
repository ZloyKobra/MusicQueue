<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    public $timestamps = false; // только created_at

    protected $fillable = [
        'title',
        'artist',
        'duration',
        'youtube_url',
        'cover_url',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    // Связи
    public function queueItems()
    {
        return $this->hasMany(QueueItem::class);
    }

    // Форматированная длительность (мм:сс)
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) return '--:--';
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
