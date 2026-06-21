@extends('layouts.app')
@section('title', 'Создать плейлист')
@section('content')
<h1 class="mb-4">Создать плейлист</h1>

<form method="POST" action="{{ route('playlists.store') }}" class="card p-4">
    @csrf
    <div class="mb-3">
        <label class="form-label">Название</label>
        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Описание</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="is_public" class="form-check-input" id="is_public" checked>
        <label class="form-check-label" for="is_public">Публичный плейлист</label>
    </div>
    <button type="submit" class="btn btn-primary">Создать</button>
</form>
@endsection
