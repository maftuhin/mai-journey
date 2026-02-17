<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = min(max($perPage, 1), 50);

        return response()->json(
            Post::query()->latest()->paginate($perPage)
        );
    }

    public function show(Post $post)
    {
        return response()->json($post);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
        ]);

        if (empty($data['excerpt'])) {
            $data['excerpt'] = mb_substr($data['content'], 0, 150) . '...';
        }

        $post = Post::create($data);
        return response()->json($post, 201);
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
        ]);

        if (empty($data['excerpt'])) {
            $data['excerpt'] = mb_substr($data['content'], 0, 150) . '...';
        }

        $post->update($data);
        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}
