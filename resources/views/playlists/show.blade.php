@extends('layouts.app')
@section('title', $playlist->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $playlist->title }}</h1>
        <p class="text-muted">Организатор: {{ $playlist->user->name }}</p>
        @if($playlist->description)
            <p>{{ $playlist->description }}</p>
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

<!-- YouTube плеер (для организатора) -->
@if($isOwner)
    <div id="player-container" class="mb-4">
        <div id="player"></div>
    </div>
@endif

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

<!-- Активная очередь -->
<h3>Очередь</h3>
<ul id="queue" class="list-group mb-4">
    @forelse($queue as $item)
        <li class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $item->id }}">
            <div>
                @if($item->status === 'playing')
                    <span class="badge bg-success me-2">▶ Играет</span>
                @endif
                <strong>{{ $item->track->artist }}</strong> — {{ $item->track->title }}
                <br><small class="text-muted">Добавил: {{ $item->adder->name }}</small>
            </div>
            <div>
                <span class="badge bg-primary me-2">{{ $item->votes_up }} 👍</span>
                @auth
                    @if(auth()->id() !== $item->added_by)
                        <form method="POST" action="{{ route('queue.vote', [$playlist, $item]) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Голосовать</button>
                        </form>
                    @endif
                    @if($isOwner)
                        @if($item->status === 'pending')
                            <form method="POST" action="{{ route('queue.play', [$playlist, $item]) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">▶ Запустить</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('queue.skip', [$playlist, $item]) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning">⏭ Пропустить</button>
                        </form>
                        <form method="POST" action="{{ route('queue.remove', $item) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">✕</button>
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
    <h5>История</h5>
    <ul class="list-group">
        @foreach($history as $item)
            <li class="list-group-item text-muted">
                <small>{{ $item->track->artist }} — {{ $item->track->title }}</small>
            </li>
        @endforeach
    </ul>
@endif

@push('scripts')
@if($isOwner)
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="{{ asset('js/youtube-player.js') }}"></script>
@endif
<script src="{{ asset('js/queue-live.js') }}"></script>
<script>
    window.playlistId = {{ $playlist->id }};
</script>
@endpush
@endsection
