<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = min(max($perPage, 1), 50);

        $paginator = GalleryItem::query()->latest()->paginate($perPage);
        $paginator->getCollection()->transform(function (GalleryItem $item) {
            $item->image_url = asset('uploads/' . ltrim($item->image_path, '/'));
            return $item;
        });

        return response()->json($paginator);
    }

    public function show(GalleryItem $galleryItem)
    {
        $galleryItem->image_url = asset('uploads/' . ltrim($galleryItem->image_path, '/'));
        return response()->json($galleryItem);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'caption' => ['required', 'string', 'max:500'],
            'image' => ['required', 'file', 'image', 'max:10240'],
        ]);

        try {
            $path = $this->compressAndStoreImage($request->file('image'));
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $item = GalleryItem::create([
            'caption' => $data['caption'],
            'image_path' => $path,
        ]);

        return response()->json($item, 201);
    }

    public function destroy(GalleryItem $galleryItem)
    {
        if ($galleryItem->image_path) {
            Storage::disk('uploads')->delete($galleryItem->image_path);
        }
        $galleryItem->delete();

        return response()->json(['message' => 'Gallery item deleted']);
    }

    private function compressAndStoreImage($uploadedFile): string
    {
        $maxBytes = 1024 * 1024;
        $mime = $uploadedFile->getMimeType();
        $tmpPath = $uploadedFile->getRealPath();

        if ($mime === 'image/gif') {
            if ($uploadedFile->getSize() > $maxBytes) {
                throw new RuntimeException('GIF must be 1MB or smaller.');
            }
            return $uploadedFile->store('gallery', 'uploads');
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmpPath),
            'image/png' => @imagecreatefrompng($tmpPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
            default => false,
        };

        if (!$source) {
            throw new RuntimeException('Unsupported image format.');
        }

        $bestData = null;
        $bestSize = PHP_INT_MAX;
        $extension = function_exists('imagewebp') ? 'webp' : 'jpg';

        $qualities = function_exists('imagewebp')
            ? [82, 74, 66, 58, 50, 45, 40]
            : [85, 76, 68, 60, 52, 45, 38];

        foreach ($qualities as $quality) {
            ob_start();
            if ($extension === 'webp') {
                imagewebp($source, null, $quality);
            } else {
                imagejpeg($source, null, $quality);
            }
            $data = ob_get_clean();
            $size = strlen($data);

            if ($size < $bestSize) {
                $bestData = $data;
                $bestSize = $size;
            }

            if ($size <= $maxBytes) {
                break;
            }
        }

        imagedestroy($source);

        if ($bestData === null || $bestSize > $maxBytes) {
            throw new RuntimeException('Image is still too large after compression.');
        }

        $filename = 'gallery/' . bin2hex(random_bytes(16)) . '.' . $extension;
        Storage::disk('uploads')->put($filename, $bestData);

        return $filename;
    }
}
