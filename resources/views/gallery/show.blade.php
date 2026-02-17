@extends('layouts.app')

@section('content')
    <a class="back-link" href="{{ route('gallery.index') }}">← Back to gallery</a>
    <article class="post-single" style="margin-top: 1rem;">
        <div class="gallery-card">
            <img src="{{ asset('uploads/'.$galleryItem->image_path) }}" alt="{{ $galleryItem->caption }}">
            <div class="gallery-card-body">
                <p>{{ $galleryItem->caption }}</p>
                <div class="post-meta">🌸 {{ $galleryItem->created_at->format('F j, Y') }}</div>
            </div>
        </div>
    </article>
@endsection
