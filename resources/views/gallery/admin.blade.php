@extends('layouts.app')

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

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
                <input id="galleryImageInput" type="file" name="image" required accept="image/*">
            </div>

            <div id="imageEditorPanel" class="image-editor-panel" hidden>
                <div class="image-editor-toolbar">
                    <button class="btn btn-secondary btn-small" type="button" id="rotateLeftBtn">Rotate Left</button>
                    <button class="btn btn-secondary btn-small" type="button" id="rotateRightBtn">Rotate Right</button>
                    <button class="btn btn-secondary btn-small" type="button" id="resetCropBtn">Reset</button>
                </div>
                <p class="image-editor-hint">Drag on image to crop area, then upload.</p>
                <div class="image-editor-canvas-wrap">
                    <img id="galleryCropImage" alt="Image editor preview">
                </div>
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        (function () {
            const form = document.querySelector('form[action="{{ route('gallery.store') }}"]');
            const fileInput = document.getElementById('galleryImageInput');
            const editorPanel = document.getElementById('imageEditorPanel');
            const image = document.getElementById('galleryCropImage');
            const rotateLeftBtn = document.getElementById('rotateLeftBtn');
            const rotateRightBtn = document.getElementById('rotateRightBtn');
            const resetCropBtn = document.getElementById('resetCropBtn');

            if (!form || !fileInput || !editorPanel || !image || typeof Cropper === 'undefined') return;

            let cropper = null;
            let blobUrl = null;
            let isSubmittingEditedImage = false;

            function destroyCropper() {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                if (blobUrl) {
                    URL.revokeObjectURL(blobUrl);
                    blobUrl = null;
                }
            }

            function initCropper(file) {
                destroyCropper();
                blobUrl = URL.createObjectURL(file);
                image.src = blobUrl;
                editorPanel.hidden = false;

                image.onload = function () {
                    cropper = new Cropper(image, {
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        responsive: true,
                        background: false,
                        checkOrientation: true
                    });
                };
            }

            fileInput.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0];
                if (!file) {
                    destroyCropper();
                    editorPanel.hidden = true;
                    return;
                }
                if (!file.type.startsWith('image/')) {
                    destroyCropper();
                    editorPanel.hidden = true;
                    return;
                }
                initCropper(file);
            });

            rotateLeftBtn.addEventListener('click', function () {
                if (cropper) cropper.rotate(-90);
            });

            rotateRightBtn.addEventListener('click', function () {
                if (cropper) cropper.rotate(90);
            });

            resetCropBtn.addEventListener('click', function () {
                if (cropper) cropper.reset();
            });

            form.addEventListener('submit', function (event) {
                if (isSubmittingEditedImage || !cropper) return;
                event.preventDefault();

                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 2560,
                    maxHeight: 2560,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                if (!canvas) {
                    form.submit();
                    return;
                }

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        form.submit();
                        return;
                    }

                    const original = fileInput.files && fileInput.files[0];
                    const extension = 'jpg';
                    const baseName = original && original.name ? original.name.replace(/\.[^.]+$/, '') : 'image';
                    const editedFile = new File([blob], baseName + '-edited.' + extension, { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(editedFile);
                    fileInput.files = dataTransfer.files;

                    isSubmittingEditedImage = true;
                    form.submit();
                }, 'image/jpeg', 0.9);
            });
        })();
    </script>
@endpush
