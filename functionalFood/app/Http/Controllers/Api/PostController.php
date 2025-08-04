<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    // Lấy danh sách bài viết public
    public function index()
    {
        $posts = Post::where('status', 'public')->orderBy('created_at', 'desc')->paginate(10);
        return PostResource::collection($posts);
    }

    // Xem chi tiết bài viết public
    public function show($id)
    {
        $post = Post::where('status', 'public')->findOrFail($id);
        return new PostResource($post);
    }
} 