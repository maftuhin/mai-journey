@extends('layouts.app')

@section('content')
    <div class="admin-title">
        <h2>Gallery Manager</h2>
    </div>
    <div class="editor-container">
        <form method="POST" action="{{ route('gallery.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Caption</label>
                <textarea name="caption" required style="min-height: 140px;">{{ old('caption') }}</textarea>
            </div>

            <div class="form-group">
                <label>Image (up to 10MB upload, saved <= 1MB)</label>
                <input type="file" name="image" required accept="image/*">
            </div>

            <div class="editor-actions">
                <button class="btn btn-primary" type="submit">Upload to Gallery</button>
            </div>
        </form>
    </div>

    <div class="admin-title" style="margin-top: 2.25rem;">
        <h2>All Gallery Items</h2>
    </div>
    <div class="gallery-admin-grid">
        @forelse($items as $item)
            <article class="gallery-admin-card">
                <a href="{{ route('gallery.show', $item) }}" style="text-decoration:none; color:inherit;">
                    <img src="{{ asset('uploads/'.$item->image_path) }}" alt="{{ $item->caption }}">
                </a>
                <div class="gallery-admin-card-body">
                    <p><a href="{{ route('gallery.show', $item) }}" style="color:inherit; text-decoration:none;">{{ $item->caption }}</a></p>
                    <form method="POST" action="{{ route('gallery.destroy', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-small" type="submit" onclick="return confirm('Delete this image?')">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty-table">
                <div class="icon">📷</div>
                <p>No gallery items yet. Upload your first image above.</p>
            </div>
        @endforelse
    </div>
    @if($items->lastPage() > 1)
        <nav class="pagination" aria-label="Admin gallery pagination">
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
