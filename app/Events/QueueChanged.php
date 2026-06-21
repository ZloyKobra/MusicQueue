<?php

namespace App\Events;

use App\Models\Playlist;
use App\Models\QueueItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Playlist $playlist,
        public QueueItem $item,
        public string $action // 'add', 'vote', 'skip', 'play', 'remove'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('playlist.' . $this->playlist->id),
        ];
    }

    public function broadcastWith(): array
    {
        // Загружаем актуальную очередь
        $queue = $this->playlist->queueItems()
            ->with(['track', 'adder'])
            ->whereIn('status', ['pending', 'playing'])
            ->orderByDesc('votes_up')
            ->orderBy('position')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'track' => [
                    'id' => $item->track->id,
                    'title' => $item->track->title,
                    'artist' => $item->track->artist,
                    'youtube_url' => $item->track->youtube_url,
                    'cover_url' => $item->track->cover_url,
                ],
                'adder' => [
                    'id' => $item->adder->id,
                    'name' => $item->adder->name,
                ],
                'votes_up' => $item->votes_up,
                'status' => $item->status,
                'position' => $item->position,
            ]);

        return [
            'action' => $this->action,
            'item_id' => $this->item->id,
            'playlist_id' => $this->playlist->id,
            'queue' => $queue,
            'timestamp' => now()->toISOString(),
        ];
    }
}
