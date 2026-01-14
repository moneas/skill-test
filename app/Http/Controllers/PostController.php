<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
    /**
     * Display a listing of active posts.
     */
    public function index(): JsonResponse
    {
        $posts = Post::active()
            ->with('user:id,name,email')
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        return response()->json($posts);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): string
    {
        return 'posts.create';
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return response()->json($post, 201);
    }

    /**
     * Display the specified active post.
     */
    public function show(Post $post): JsonResponse|Response
    {
        // Check if post is active (not draft and published, not scheduled)
        if ($post->is_draft || !$post->published_at || $post->published_at->isFuture()) {
            abort(404);
        }

        $post->load('user:id,name,email');

        return response()->json($post);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Request $request, Post $post): string|Response
    {
        // Only the post author can access this route
        if ($request->user()->id !== $post->user_id) {
            abort(403);
        }

        return 'posts.edit';
    }

    /**
     * Update the specified post in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse|Response
    {
        // Only the post author can update the post
        if ($request->user()->id !== $post->user_id) {
            abort(403);
        }

        $post->update($request->validated());

        return response()->json($post);
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Request $request, Post $post): Response
    {
        // Only the post author can delete the post
        if ($request->user()->id !== $post->user_id) {
            abort(403);
        }

        $post->delete();

        return response()->noContent();
    }
}
