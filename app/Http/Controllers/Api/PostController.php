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
        try {
            $posts = Post::where('status', 'public')->orderBy('created_at', 'desc')->paginate(10);
            
            if ($posts->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Không có bài viết nào',
                    'data' => []
                ], 200);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách bài viết thành công',
                'data' => PostResource::collection($posts),
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách bài viết: ' . $e->getMessage()
            ], 500);
        }
    }

    // Xem chi tiết bài viết public
    public function show($id)
    {
        try {
            $post = Post::where('status', 'public')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin bài viết thành công',
                'data' => new PostResource($post)
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin bài viết: ' . $e->getMessage()
            ], 500);
        }
    }
} 