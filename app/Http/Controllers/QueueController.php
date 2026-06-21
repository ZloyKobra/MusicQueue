<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\QueueItem;
use App\Models\Track;
use App\Events\QueueChanged;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    // Голосование за трек
    public function vote(Playlist $playlist, QueueItem $item)
    {
        abort_if($item->playlist_id !== $playlist->id, 404);
        abort_unless(in_array($item->status, ['pending', 'playing']), 422);

        $item->increment('votes_up');
        
        QueueChanged::dispatch($playlist, $item, 'vote');

        return back()->with('success', 'Голос учтён');
    }

    // Пропуск трека (только организатор)
    public function skip(Playlist $playlist, QueueItem $item)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
        abort_if($item->playlist_id !== $playlist->id, 404);

        $item->update(['status' => 'skipped']);

        // Публикуем событие
        QueueChanged::dispatch($playlist, $item, 'skip');

        return back()->with('success', 'Трек пропущен');
    }

    // Запуск трека (только организатор)
    public function play(Playlist $playlist, QueueItem $item)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
        abort_if($item->playlist_id !== $playlist->id, 404);

        // Останавливаем текущий играющий трек
        $playlist->queueItems()
            ->where('status', 'playing')
            ->update(['status' => 'played', 'played_at' => now()]);

        // Запускаем новый
        $item->update([
            'status' => 'playing',
            'played_at' => now(),
        ]);

        // Публикуем событие
        QueueChanged::dispatch($playlist, $item, 'play');

        return back()->with('success', 'Трек запущен');
    }

    // Удаление трека из очереди
    public function remove(Playlist $playlist, QueueItem $item)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
        abort_if($item->playlist_id !== $playlist->id, 404);

        $item->delete();

        // Публикуем событие
        QueueChanged::dispatch($playlist, $item, 'remove');

        return back()->with('success', 'Трек удалён из очереди');
    }

    // Извлечение ID видео из YouTube URL
    private function extractYoutubeThumbnail(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $url, $matches);
        return isset($matches[1]) ? "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg" : null;
    }

    public function addTrack(Request $request, Playlist $playlist)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'youtube_url' => 'required|url',
        ]);

        $track = Track::firstOrCreate(
            ['youtube_url' => $validated['youtube_url']],
            [
                'title' => $validated['title'],
                'artist' => $validated['artist'],
                'duration' => null,
                'cover_url' => $this->extractYoutubeThumbnail($validated['youtube_url']),
            ]
        );

        $maxPosition = $playlist->queueItems()->max('position') ?? 0;

        $item = QueueItem::create([
            'playlist_id' => $playlist->id,
            'track_id' => $track->id,
            'added_by' => auth()->id(),
            'position' => $maxPosition + 1,
            'status' => 'pending',
            'votes_up' => 1,
        ]);

        // Публикуем событие
        QueueChanged::dispatch($playlist, $item, 'add');

        return back()->with('success', 'Трек добавлен в очередь');
    }

    public function playNext(Playlist $playlist)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
    
        // Текущий играющий трек
        $current = $playlist->queueItems()
            ->where('status', 'playing')
            ->first();
    
        if ($current) {
            $current->update(['status' => 'played', 'played_at' => now()]);
        }
    
        // Следующий трек (с наибольшим количеством голосов)
        $next = $playlist->queueItems()
            ->where('status', 'pending')
            ->orderByDesc('votes_up')
            ->orderBy('position')
            ->first();
    
        if ($next) {
            $next->update(['status' => 'playing', 'played_at' => now()]);
            QueueChanged::dispatch($playlist, $next, 'play');
        }
        
        return response()->json(['success' => true]);
    }
}
