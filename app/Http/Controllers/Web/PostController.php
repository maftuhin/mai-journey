<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::query()->latest()->get();
        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function editor(?Post $post = null)
    {
        return view('posts.editor', ['post' => $post]);
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

        Post::create($data);

        return redirect()->route('posts.editor')->with('success', 'Story published successfully!');
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

        return redirect()->route('posts.editor.edit', $post)->with('success', 'Story updated successfully!');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Story deleted.');
    }
}
