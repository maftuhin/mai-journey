@extends('layouts.app')

@section('content')
    <a class="back-link" href="{{ route('posts.index') }}">← Back to all stories</a>
    <article class="post-single">
        <h1>{{ $post->title }}</h1>
        <div class="post-meta">🌸 {{ $post->created_at->format('F j, Y') }}</div>
        <div class="post-content">
            @foreach (preg_split("/\n\s*\n/", $post->content) as $paragraph)
                @if (trim($paragraph) !== '')
                    <p>{!! nl2br(e(trim($paragraph))) !!}</p>
                @endif
            @endforeach
        </div>
    </article>
@endsection
