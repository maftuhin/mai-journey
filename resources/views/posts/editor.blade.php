@extends('layouts.app')

@section('content')
    <div class="admin-title">
        <h2>{{ $post ? 'Edit Story' : 'Write New Story' }}</h2>
    </div>
    <div class="editor-container">
        <form method="POST" action="{{ $post ? route('posts.update', $post) : route('posts.store') }}">
            @csrf
            @if($post) @method('PUT') @endif

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required value="{{ old('title', $post?->title) }}">
            </div>

            <div class="form-group">
                <label>Story</label>
                <textarea name="content" required>{{ old('content', $post?->content) }}</textarea>
            </div>

            <div class="form-group">
                <label>Excerpt (optional)</label>
                <textarea name="excerpt" style="min-height: 120px;">{{ old('excerpt', $post?->excerpt) }}</textarea>
            </div>

            <div class="editor-actions">
                <a href="{{ route('posts.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $post ? 'Update Story' : 'Publish Story' }}</button>
            </div>
        </form>
    </div>
@endsection
