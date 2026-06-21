<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\QueueItem;
use App\Models\Track;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    // Добавление трека в очередь
    public function addTrack(Request $request, Playlist $playlist)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'youtube_url' => 'required|url',
        ]);

        // Ищем трек или создаём новый
        $track = Track::firstOrCreate(
            ['youtube_url' => $validated['youtube_url']],
            [
                'title' => $validated['title'],
                'artist' => $validated['artist'],
                'duration' => null, // можно парсить позже
                'cover_url' => $this->extractYoutubeThumbnail($validated['youtube_url']),
            ]
        );

        $maxPosition = $playlist->queueItems()->max('position') ?? 0;

        QueueItem::create([
            'playlist_id' => $playlist->id,
            'track_id' => $track->id,
            'added_by' => auth()->id(),
            'position' => $maxPosition + 1,
            'status' => 'pending',
            'votes_up' => 1, // автоматический голос от добавившего
        ]);

        return back()->with('success', 'Трек добавлен в очередь');
    }

    // Голосование за трек
    public function vote(Playlist $playlist, QueueItem $item)
    {
        abort_if($item->playlist_id !== $playlist->id, 404);
        abort_unless(in_array($item->status, ['pending', 'playing']), 422);

        $item->increment('votes_up');

        return back()->with('success', 'Голос учтён');
    }

    // Пропуск трека (только организатор)
    public function skip(Playlist $playlist, QueueItem $item)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
        abort_if($item->playlist_id !== $playlist->id, 404);

        $item->update(['status' => 'skipped']);

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

        return back()->with('success', 'Трек запущен');
    }

    // Удаление трека из очереди
    public function remove(Playlist $playlist, QueueItem $item)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
        abort_if($item->playlist_id !== $playlist->id, 404);

        $item->delete();

        return back()->with('success', 'Трек удалён из очереди');
    }

    // Извлечение ID видео из YouTube URL
    private function extractYoutubeThumbnail(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $url, $matches);
        return isset($matches[1]) ? "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg" : null;
    }
}
