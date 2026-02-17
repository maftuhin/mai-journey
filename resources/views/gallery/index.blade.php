@extends('layouts.app')

@section('content')
    <h2 class="section-title">Photo Gallery</h2>
    <div class="gallery-grid">
        @forelse($items as $item)
            <article class="gallery-card">
                <a href="{{ route('gallery.show', $item) }}" style="text-decoration:none; color:inherit;">
                    <img src="{{ asset('uploads/'.$item->image_path) }}" alt="{{ $item->caption }}">
                    <div class="gallery-card-body">
                        <p>{{ $item->caption }}</p>
                    </div>
                </a>
            </article>
        @empty
            <div class="empty-state">
                <div class="icon">📷</div>
                <h3>No gallery photos yet</h3>
                <p>Upload beautiful moments from admin gallery page.</p>
            </div>
        @endforelse
    </div>
    @if($items->lastPage() > 1)
        <nav class="pagination" aria-label="Gallery pagination">
            @if($items->onFirstPage() === false)
                <a href="{{ $items->previousPageUrl() }}" class="pagination-link">← Previous</a>
            @endif
            <span class="pagination-info">Page {{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
            @if($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}" class="pagination-link">Next →</a>
            @endif
        </nav>
    @endif
@endsection
