<?php

namespace App\Http\Controllers;

use App\Events\QueueChanged;
use App\Models\Playlist;
use App\Models\QueueItem;
use App\Models\Track;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function addTrack(Request $request, Playlist $playlist)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'track_url' => 'required|url',
        ]);

        $track = Track::firstOrCreate(
            ['track_url' => $validated['track_url']],
            [
                'title' => $validated['title'],
                'artist' => $validated['artist'],
                'duration' => null,
                'cover_url' => null,
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

        QueueChanged::dispatch($playlist, $item, 'add');

        return back()->with('success', 'Трек добавлен в очередь');
    }

    public function vote(Playlist $playlist, QueueItem $item)
    {
        if ($item->playlist_id !== $playlist->id) {
            abort(404);
        }

        if (!in_array($item->status, ['pending', 'playing'])) {
            abort(422, 'Нельзя голосовать за этот трек');
        }

        $item->increment('votes_up');
        QueueChanged::dispatch($playlist, $item, 'vote');

        return back()->with('success', 'Голос учтён');
    }

    public function play(Playlist $playlist, QueueItem $item)
    {
        if (auth()->id() !== $playlist->user_id) {
            abort(403, 'Только организатор может управлять очередью');
        }

        if ($item->playlist_id !== $playlist->id) {
            abort(404);
        }

        // Останавливаем текущий играющий трек
        $playlist->queueItems()
            ->where('status', 'playing')
            ->update(['status' => 'played', 'played_at' => now()]);

        // Запускаем новый
        $item->update([
            'status' => 'playing',
            'played_at' => now(),
        ]);

        QueueChanged::dispatch($playlist, $item, 'play');

        return back()->with('success', 'Трек запущен');
    }

    public function skip(Playlist $playlist, QueueItem $item)
    {
        if (auth()->id() !== $playlist->user_id) {
            abort(403, 'Только организатор может пропускать треки');
        }

        if ($item->playlist_id !== $playlist->id) {
            abort(404);
        }

        $item->update(['status' => 'skipped']);
        QueueChanged::dispatch($playlist, $item, 'skip');

        return back()->with('success', 'Трек пропущен');
    }
}
