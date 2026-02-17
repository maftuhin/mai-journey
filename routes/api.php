<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('api.token')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    Route::get('/gallery', [GalleryController::class, 'index']);
    Route::get('/gallery/{galleryItem}', [GalleryController::class, 'show']);
    Route::post('/gallery', [GalleryController::class, 'store']);
    Route::delete('/gallery/{galleryItem}', [GalleryController::class, 'destroy']);
});
