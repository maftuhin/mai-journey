@extends('layouts.app')

@section('content')
    <h2 class="section-title">Latest Stories</h2>
    <div class="posts-grid">
    @forelse($posts as $post)
        <article class="post-card">
            <h2>{{ $post->title }}</h2>
            <div class="post-meta">🌸 {{ $post->created_at->format('F j, Y') }}</div>
            <p class="post-excerpt">{{ $post->excerpt }}</p>
            <a class="read-more" href="{{ route('posts.show', $post) }}">Read More</a>
        </article>
    @empty
        <div class="empty-state">
            <div class="icon">🌸</div>
            <h3>No stories yet</h3>
            <p>The journey is just beginning...</p>
        </div>
    @endforelse
    </div>
@endsection
