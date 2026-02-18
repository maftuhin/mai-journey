<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $items = GalleryItem::query()->latest()->paginate(5);
        return view('gallery.index', compact('items'));
    }

    public function admin(Request $request)
    {
        $items = GalleryItem::query()->latest()->paginate(5);
        return view('gallery.admin', compact('items'));
    }

    public function show(GalleryItem $galleryItem)
    {
        return view('gallery.show', compact('galleryItem'));
    }

    public function store(Request $request)
    {
        $maxUploadKb = (int) config('gallery.max_upload_kb', 10240);

        $data = $request->validate([
            'caption' => ['required', 'string', 'max:500'],
        ]);

        $errorCode = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            return back()->withErrors(['image' => $this->uploadErrorMessage($errorCode)])->withInput();
        }

        $file = $request->file('image');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            $error = $file instanceof UploadedFile
                ? $this->uploadErrorMessage($file->getError())
                : 'No image file received.';
            return back()->withErrors(['image' => $error])->withInput();
        }

        $validator = Validator::make(
            ['image' => $file],
            ['image' => ['required', 'file', 'image', "max:{$maxUploadKb}"]],
            [
                'image.image' => 'Image format is invalid. Use JPG, PNG, GIF, or WEBP.',
                'image.max' => "Image is too large. Maximum upload is {$maxUploadKb}KB.",
            ]
        );
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $path = $this->compressAndStoreImage($file);
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors(['image' => $this->friendlyUploadError($e)])->withInput();
        }

        GalleryItem::create([
            'caption' => $data['caption'],
            'image_path' => $path,
        ]);

        return redirect()->route('gallery.admin')->with('success', 'Gallery item added successfully.');
    }

    public function destroy(GalleryItem $galleryItem)
    {
        if ($galleryItem->image_path) {
            Storage::disk('uploads')->delete($galleryItem->image_path);
        }
        $galleryItem->delete();

        return redirect()->route('gallery.admin')->with('success', 'Gallery item deleted.');
    }

    private function compressAndStoreImage(UploadedFile $uploadedFile): string
    {
        $maxBytes = (int) config('gallery.max_saved_bytes', 1024 * 1024);
        $mime = $uploadedFile->getMimeType();
        $tmpPath = $uploadedFile->getRealPath();

        if (!extension_loaded('gd')) {
            throw new RuntimeException('Server image library (GD) is not enabled.');
        }

        if (!is_dir(public_path('uploads/gallery')) || !is_writable(public_path('uploads/gallery'))) {
            throw new RuntimeException('Upload folder is not writable: public/uploads/gallery');
        }

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

        $source = $this->normalizeImageOrientation($source, $mime, $tmpPath);

        $bestData = null;
        $bestSize = PHP_INT_MAX;
        $extension = function_exists('imagewebp') ? 'webp' : 'jpg';
        $qualities = function_exists('imagewebp')
            ? [82, 74, 66, 58, 50, 45, 40]
            : [85, 76, 68, 60, 52, 45, 38];
        $maxDimensions = [2560, 2048, 1600, 1280, 1080, 900, 768];

        foreach ($maxDimensions as $maxDimension) {
            $working = $source;
            $width = imagesx($source);
            $height = imagesy($source);
            $largest = max($width, $height);

            if ($largest > $maxDimension) {
                $ratio = $maxDimension / $largest;
                $newWidth = max(1, (int) round($width * $ratio));
                $newHeight = max(1, (int) round($height * $ratio));
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($resized, true);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                $working = $resized;
            }

            foreach ($qualities as $quality) {
                ob_start();
                if ($extension === 'webp') {
                    imagewebp($working, null, $quality);
                } else {
                    imagejpeg($working, null, $quality);
                }
                $data = ob_get_clean();
                $size = strlen($data);

                if ($size < $bestSize) {
                    $bestData = $data;
                    $bestSize = $size;
                }
                if ($size <= $maxBytes) {
                    break 2;
                }
            }

            if ($working !== $source) {
                imagedestroy($working);
            }
        }

        imagedestroy($source);

        if ($bestData === null || $bestSize > $maxBytes) {
            throw new RuntimeException('Image is still too large after compression.');
        }

        $filename = 'gallery/' . bin2hex(random_bytes(16)) . '.' . $extension;
        if (!Storage::disk('uploads')->put($filename, $bestData)) {
            throw new RuntimeException('Failed to save image file to uploads folder.');
        }

        return $filename;
    }

    private function friendlyUploadError(Throwable $e): string
    {
        $message = trim($e->getMessage());
        if ($message !== '') {
            return $message;
        }

        return 'Upload failed due to a server error. Check Laravel log for details.';
    }

    private function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Upload failed: file is larger than server upload limit.',
            UPLOAD_ERR_PARTIAL => 'Upload failed: file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No image uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Upload failed: temporary folder is missing on server.',
            UPLOAD_ERR_CANT_WRITE => 'Upload failed: server cannot write uploaded file.',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by a server extension.',
            default => 'Upload failed. Please try again.',
        };
    }

    private function normalizeImageOrientation($image, string $mime, string $tmpPath)
    {
        if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($tmpPath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => $this->rotateImage($image, 180),
            6 => $this->rotateImage($image, -90),
            8 => $this->rotateImage($image, 90),
            default => $image,
        };
    }

    private function rotateImage($image, int $angle)
    {
        $rotated = @imagerotate($image, $angle, 0);
        if (!$rotated) {
            return $image;
        }

        imagedestroy($image);
        return $rotated;
    }
}
