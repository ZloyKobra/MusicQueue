<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    // Список всех публичных плейлистов + свои
    public function index()
    {
        $playlists = Playlist::with('user')
            ->where('is_public', true)
            ->orWhere('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('playlists.index', compact('playlists'));
    }

    // Форма создания
    public function create()
    {
        return view('playlists.create');
    }

    // Сохранение нового плейлиста
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $playlist = auth()->user()->playlists()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        return redirect()->route('playlists.show', $playlist->slug)
            ->with('success', 'Плейлист создан');
    }

    // Просмотр одного плейлиста (публичная страница с очередью)
    public function show(string $slug)
    {
        $playlist = Playlist::where('slug', $slug)
            ->with(['user', 'queueItems.track', 'queueItems.adder'])
            ->firstOrFail();

        // Активная очередь: pending + playing, сортировка по голосам
        $queue = $playlist->queueItems()
            ->with(['track', 'adder'])
            ->whereIn('status', ['pending', 'playing'])
            ->orderByDesc('votes_up')
            ->orderBy('position')
            ->get();

        // История сыгранных треков
        $history = $playlist->queueItems()
            ->with(['track', 'adder'])
            ->where('status', 'played')
            ->latest('played_at')
            ->take(10)
            ->get();

        $isOwner = auth()->check() && auth()->id() === $playlist->user_id;

        return view('playlists.show', compact('playlist', 'queue', 'history', 'isOwner'));
    }

    // Форма редактирования
    public function edit(Playlist $playlist)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
        return view('playlists.edit', compact('playlist'));
    }

    // Обновление
    public function update(Request $request, Playlist $playlist)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $playlist->update($validated);

        return redirect()->route('playlists.show', $playlist->slug)
            ->with('success', 'Плейлист обновлён');
    }

    // Удаление
    public function destroy(Playlist $playlist)
    {
        abort_if(auth()->id() !== $playlist->user_id, 403);
        $playlist->delete();

        return redirect()->route('playlists.index')
            ->with('success', 'Плейлист удалён');
    }
}
