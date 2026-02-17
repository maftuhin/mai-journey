<?php

namespace App\Console\Commands;

use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-legacy-data {--legacy-db=../includes/database.sqlite}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import posts, gallery, and admin user from legacy app';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $legacyPath = base_path($this->option('legacy-db'));
        if (!File::exists($legacyPath)) {
            $this->error("Legacy database not found: {$legacyPath}");
            return self::FAILURE;
        }

        DB::statement("ATTACH DATABASE '{$legacyPath}' as legacy");

        $this->importUsers();
        $this->importPosts();
        $this->importGallery();
        $this->copyGalleryFiles();

        DB::statement('DETACH DATABASE legacy');

        $this->info('Legacy data import completed.');
        return self::SUCCESS;
    }

    private function importUsers(): void
    {
        $admins = DB::select('SELECT username, password FROM legacy.admin');
        foreach ($admins as $admin) {
            $username = (string) $admin->username;
            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => ucfirst($username),
                    'email' => $username . '@maijourney.local',
                    'password' => (string) $admin->password,
                ]
            );
        }
    }

    private function importPosts(): void
    {
        $rows = DB::select('SELECT id, title, content, excerpt, created_at, updated_at FROM legacy.posts');
        foreach ($rows as $row) {
            Post::updateOrCreate(
                ['id' => (int) $row->id],
                [
                    'title' => (string) $row->title,
                    'content' => (string) $row->content,
                    'excerpt' => $row->excerpt ? (string) $row->excerpt : null,
                    'created_at' => $row->created_at ?: now(),
                    'updated_at' => $row->updated_at ?: now(),
                ]
            );
        }
    }

    private function importGallery(): void
    {
        $rows = DB::select('SELECT id, caption, image_path, created_at FROM legacy.gallery');
        foreach ($rows as $row) {
            $path = ltrim((string) $row->image_path, '/');
            if (str_starts_with($path, 'uploads/')) {
                $path = substr($path, strlen('uploads/'));
            }

            GalleryItem::updateOrCreate(
                ['id' => (int) $row->id],
                [
                    'caption' => (string) $row->caption,
                    'image_path' => $path,
                    'created_at' => $row->created_at ?: now(),
                    'updated_at' => $row->created_at ?: now(),
                ]
            );
        }
    }

    private function copyGalleryFiles(): void
    {
        $source = base_path('../uploads/gallery');
        $target = public_path('uploads/gallery');

        if (!File::isDirectory($source)) {
            $this->warn("Legacy gallery folder not found: {$source}");
            return;
        }

        File::ensureDirectoryExists($target);
        foreach (File::files($source) as $file) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $file->getFilename();
            if (!File::exists($targetPath)) {
                File::copy($file->getPathname(), $targetPath);
            }
        }
    }
}
