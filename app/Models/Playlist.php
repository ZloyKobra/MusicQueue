<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // Связи
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function queueItems()
    {
        return $this->hasMany(QueueItem::class);
    }

    // Активная очередь (pending + playing)
    public function activeQueue()
    {
        return $this->queueItems()
            ->whereIn('status', ['pending', 'playing'])
            ->orderByDesc('votes_up')
            ->orderBy('position');
    }
}
