<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;

class AdminPostController extends Controller
{
    // Lấy danh sách tất cả bài viết
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->paginate(10);
        return PostResource::collection($posts);
    }

    // Tạo mới bài viết
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|file|image|max:2048',
            'status' => 'required|in:public,draft',
        ]);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('public/posts');
        }
        $post = Post::create($data);
        return new PostResource($post);
    }

    // Xem chi tiết bài viết
    public function show($id)
    {
        $post = Post::findOrFail($id);
        return new PostResource($post);
    }

    // Cập nhật bài viết
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required',
            'image' => 'nullable|file|image|max:2048',
            'status' => 'sometimes|required|in:public,draft',
        ]);
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($post->image) {
                Storage::delete($post->image);
            }
            $data['image'] = $request->file('image')->store('public/posts');
        }
        $post->update($data);
        return new PostResource($post);
    }

    // Xóa bài viết
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        if ($post->image) {
            Storage::delete($post->image);
        }
        $post->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // Chuyển đổi trạng thái bài viết
    public function toggleStatus($id)
    {
        $post = Post::findOrFail($id);
        $post->status = $post->status === 'public' ? 'draft' : 'public';
        $post->save();
        
        return new PostResource($post);
    }
} 