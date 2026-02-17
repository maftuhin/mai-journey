<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\GalleryController;
use App\Http\Controllers\Web\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

    Route::get('/editor', [PostController::class, 'editor'])->name('posts.editor');
    Route::get('/editor/{post}', [PostController::class, 'editor'])->name('posts.editor.edit');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/gallery/{galleryItem}', [GalleryController::class, 'show'])->name('gallery.show');
    Route::get('/admin/gallery', [GalleryController::class, 'admin'])->name('gallery.admin');
    Route::post('/admin/gallery', [GalleryController::class, 'store'])->name('gallery.store');
    Route::delete('/admin/gallery/{galleryItem}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
});
