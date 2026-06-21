<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueItem extends Model
{
    use HasFactory;

    protected $table = 'queue_items';

    protected $fillable = [
        'playlist_id',
        'track_id',
        'added_by',
        'position',
        'status',
        'votes_up',
        'played_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'votes_up' => 'integer',
        'played_at' => 'datetime',
    ];

    // Связи
    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function adder()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // Скоупы для удобной фильтрации
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePlaying($query)
    {
        return $query->where('status', 'playing');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'playing']);
    }
}
