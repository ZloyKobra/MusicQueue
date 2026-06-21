@extends('layouts.app')
@section('title', $playlist->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $playlist->title }}</h1>
        <p class="text-muted mb-0">Организатор: {{ $playlist->user->name }}</p>
        @if($playlist->description)
            <p class="mt-2">{{ $playlist->description }}</p>
        @endif
    </div>
    @if($isOwner)
        <div>
            <a href="{{ route('playlists.edit', $playlist) }}" class="btn btn-sm btn-outline-secondary">Редактировать</a>
            <form method="POST" action="{{ route('playlists.destroy', $playlist) }}" class="d-inline" onsubmit="return confirm('Удалить плейлист?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Удалить</button>
            </form>
        </div>
    @endif
</div>

<!-- YouTube плеер -->
<div id="player-container" class="mb-4">
    <div id="player"></div>
</div>

<!-- Форма добавления трека -->
@auth
<div class="card mb-4">
    <div class="card-body">
        <h5>Добавить трек в очередь</h5>
        <form method="POST" action="{{ route('queue.add', $playlist) }}" class="row g-2">
            @csrf
            <div class="col-md-4">
                <input type="text" name="title" class="form-control" placeholder="Название трека" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="artist" class="form-control" placeholder="Исполнитель" required>
            </div>
            <div class="col-md-4">
                <input type="url" name="youtube_url" class="form-control" placeholder="YouTube URL" required>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100">+</button>
            </div>
        </form>
    </div>
</div>
@endauth

<!-- Статус WebSocket -->
<div id="ws-status" class="mb-3">
    <span class="badge bg-warning">Connecting...</span>
</div>

<!-- Очередь -->
<h3 class="mb-3">Очередь</h3>
<ul id="queue-list" class="list-group mb-4">
    @forelse($queue as $item)
        <li class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $item->id }}">
            <div>
                @if($item->status === 'playing')
                    <span class="badge bg-success me-2">▶ Играет</span>
                @endif
                <strong>{{ $item->track->artist }}</strong> — {{ $item->track->title }}
                <br><small class="text-muted">Добавил: {{ $item->adder->name }}</small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">{{ $item->votes_up }} 👍</span>
                @auth
                    @if(auth()->id() !== $item->added_by)
                        <form method="POST" action="{{ route('queue.vote', [$playlist, $item]) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Голосовать</button>
                        </form>
                    @endif
                    @if($isOwner && $item->status === 'pending')
                        <form method="POST" action="{{ route('queue.play', [$playlist, $item]) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">▶</button>
                        </form>
                        <form method="POST" action="{{ route('queue.skip', [$playlist, $item]) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning">⏭</button>
                        </form>
                    @endif
                @endauth
            </div>
        </li>
    @empty
        <li class="list-group-item text-muted">Очередь пуста</li>
    @endforelse
</ul>

<!-- История -->
@if($history->count())
    <h5 class="mt-4">История</h5>
    <ul class="list-group">
        @foreach($history as $item)
            <li class="list-group-item text-muted">
                <small>{{ $item->track->artist }} — {{ $item->track->title }}</small>
            </li>
        @endforeach
    </ul>
@endif

@push('scripts')
<script src="https://www.youtube.com/iframe_api"></script>
<script>
// YouTube Player
window.player = null;
window.currentVideoId = null;

function onYouTubeIframeAPIReady() {
    window.player = new YT.Player('player', {
        height: '360',
        width: '640',
        videoId: '',
        playerVars: {
            'autoplay': 0,
            'controls': 1,
            'rel': 0
        }
    });
}

window.extractVideoId = function(url) {
    if (!url) return null;
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
    return match ? match[1] : null;
};

window.playTrack = function(videoId) {
    console.log('playTrack called with:', videoId);
    const id = window.extractVideoId(videoId);
    console.log('Extracted ID:', id);
    
    if (id && window.player && typeof window.player.loadVideoById === 'function') {
        if (id !== window.currentVideoId) {
            window.player.loadVideoById(id);
            window.currentVideoId = id;
            console.log('Playing:', id);
        }
    }
};


// WebSocket
const playlistId = {{ $playlist->id }};
const wsUrl = `wss://192.168.23.128:8443/ws/playlist/${playlistId}`;

function connectWebSocket() {
    const ws = new WebSocket(wsUrl);
    
    ws.onopen = () => {
        console.log('WebSocket connected');
        document.getElementById('ws-status').innerHTML = 
            '<span class="badge bg-success">● Live</span>';
    };
    
    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        console.log('Queue update:', data);
        updateQueue(data.queue);
        
        // Автоматическое воспроизведение
        const playingItem = data.queue.find(item => item.status === 'playing');
        if (playingItem && playingItem.track && playingItem.track.youtube_url) {
            playTrack(playingItem.track.youtube_url);
        }
    };
    
    ws.onclose = () => {
        console.log('WebSocket disconnected');
        document.getElementById('ws-status').innerHTML = 
            '<span class="badge bg-danger">✕ Disconnected. Reconnecting...</span>';
        setTimeout(connectWebSocket, 3000);
    };
    
    ws.onerror = (error) => {
        console.error('WebSocket error:', error);
    };
}

function updateQueue(queue) {
    const list = document.getElementById('queue-list');
    if (!queue || queue.length === 0) {
        list.innerHTML = '<li class="list-group-item text-muted">Очередь пуста</li>';
        return;
    }
    
    list.innerHTML = queue.map(item => `
        <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${item.id}">
            <div>
                ${item.status === 'playing' ? '<span class="badge bg-success me-2">▶ Играет</span>' : ''}
                <strong>${item.track.artist}</strong> — ${item.track.title}
                <br><small class="text-muted">Добавил: ${item.adder.name}</small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">${item.votes_up} 👍</span>
            </div>
        </li>
    `).join('');
}

// Запуск при загрузке
connectWebSocket();
</script>
@endpush
@endsection
