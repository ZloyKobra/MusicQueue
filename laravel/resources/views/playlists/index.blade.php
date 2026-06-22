@extends('layouts.app')
@section('title', 'Плейлисты')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Плейлисты</h1>
    @auth
        <a href="{{ route('playlists.create') }}" class="btn btn-primary">+ Создать</a>
    @endauth
</div>

<div class="row">
    @forelse($playlists as $playlist)
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $playlist->title }}</h5>
                    <p class="card-text text-muted">{{ $playlist->description }}</p>
                    <p class="card-text"><small>Организатор: {{ $playlist->user->name }}</small></p>
                    <p class="card-text">
                        <span class="badge bg-info">{{ $playlist->queueItems->count() }} треков</span>
                        @if($playlist->is_public)
                            <span class="badge bg-success">Публичный</span>
                        @endif
                    </p>
                    <a href="{{ route('playlists.show', $playlist->slug) }}" class="btn btn-primary btn-sm">Открыть</a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">Пока нет плейлистов</p>
    @endforelse
</div>

{{ $playlists->links() }}
@endsection
