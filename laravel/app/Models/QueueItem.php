<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'track_id',
        'added_by',
        'position',
        'status',
        'votes_up',
        'played_at',
    ];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'votes_up' => 'integer',
        ];
    }

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
}
