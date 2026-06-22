<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::where('is_public', true)
            ->with('user')
            ->latest()
            ->paginate(12);

        return view('playlists.index', compact('playlists'));
    }

    public function create()
    {
        return view('playlists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $playlist = $request->user()->playlists()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Плейлист создан!');
    }

    public function show(string $slug)
    {
        $playlist = Playlist::where('slug', $slug)
            ->with(['user', 'queueItems'])
            ->firstOrFail();

        $queue = $playlist->queueItems()
            ->with(['track', 'adder'])
            ->whereIn('status', ['pending', 'playing'])
            ->orderByDesc('votes_up')
            ->orderBy('position')
            ->get();

        $history = $playlist->queueItems()
            ->with(['track', 'adder'])
            ->where('status', 'played')
            ->latest('played_at')
            ->take(10)
            ->get();

        $isOwner = auth()->check() && auth()->id() === $playlist->user_id;

        return view('playlists.show', compact('playlist', 'queue', 'history', 'isOwner'));
    }

    public function edit(Playlist $playlist)
    {
        $this->authorize('update', $playlist);
        return view('playlists.edit', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $playlist->update($validated);

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Плейлист обновлён!');
    }

    public function destroy(Playlist $playlist)
    {
        $this->authorize('delete', $playlist);
        $playlist->delete();

        return redirect()->route('playlists.index')
            ->with('success', 'Плейлист удалён!');
    }
}
