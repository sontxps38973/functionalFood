<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminPostController extends Controller
{
    // Lấy danh sách tất cả bài viết
    public function index()
    {
        try {
            $posts = Post::orderBy('created_at', 'desc')->paginate(10);
            
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

    // Tạo mới bài viết
    public function store(Request $request)
    {
        try {
            // Log dữ liệu đầu vào
            Log::info('Post store request:', [
                'request_data' => $request->all(),
                'request_files' => $request->hasFile('image') ? 'Has image file' : 'No image file'
            ]);
            
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required',
                'image' => 'nullable|file|image|max:2048',
                'status' => 'required|in:public,draft',
            ]);
            
            // Log dữ liệu sau validation
            Log::info('Validated data for post creation:', $data);
            
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('posts', 'public');
                Log::info('Image stored for new post:', ['image_path' => $data['image']]);
            }
            
            $post = Post::create($data);
            
            Log::info('Post created successfully:', [
                'post_id' => $post->id,
                'title' => $post->title,
                'status' => $post->status
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo bài viết thành công',
                'data' => new PostResource($post)
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Post store error:', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo bài viết: ' . $e->getMessage()
            ], 500);
        }
    }

    // Xem chi tiết bài viết
    public function show($id)
    {
        try {
            // Log dữ liệu đầu vào
            Log::info('Post show request:', [
                'post_id' => $id
            ]);
            
            $post = Post::findOrFail($id);
            
            Log::info('Post found successfully:', [
                'post_id' => $post->id,
                'title' => $post->title,
                'status' => $post->status
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin bài viết thành công',
                'data' => new PostResource($post)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Post show error:', [
                'post_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin bài viết: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cập nhật bài viết
    public function update(Request $request, $id)
    {
        try {
            // Log dữ liệu đầu vào
            Log::info('Post update request:', [
                'post_id' => $id,
                'request_data' => $request->all(),
                'request_input' => $request->input(),
                'request_files' => $request->hasFile('image') ? 'Has image file' : 'No image file',
                'content_type' => $request->header('Content-Type')
            ]);
            
            $post = Post::findOrFail($id);
            
            // Log thông tin bài viết hiện tại
            Log::info('Current post data:', [
                'post_id' => $post->id,
                'current_title' => $post->title,
                'current_content' => $post->content,
                'current_status' => $post->status,
                'current_image' => $post->image
            ]);
            
            // Log dữ liệu raw để debug
            Log::info('Raw request data:', [
                'all' => $request->all(),
                'input' => $request->input(),
                'has_title' => $request->has('title'),
                'has_content' => $request->has('content'),
                'has_status' => $request->has('status'),
                'title_value' => $request->input('title'),
                'content_value' => $request->input('content'),
                'status_value' => $request->input('status')
            ]);
            
            // Xử lý dữ liệu từ cả JSON và FormData
            $updateData = [];
            
            // Lấy dữ liệu từ request body
            $requestBody = $request->getContent();
            Log::info('Request body content:', ['body' => $requestBody]);
            
            // Thử parse JSON nếu có
            if ($requestBody && $request->header('Content-Type') === 'application/json') {
                $jsonData = json_decode($requestBody, true);
                Log::info('Parsed JSON data:', $jsonData);
                
                if (is_array($jsonData)) {
                    if (isset($jsonData['title']) && $jsonData['title'] !== null && $jsonData['title'] !== '') {
                        $updateData['title'] = $jsonData['title'];
                        Log::info('Title from JSON will be updated:', ['new_title' => $jsonData['title']]);
                    }
                    
                    if (isset($jsonData['content']) && $jsonData['content'] !== null && $jsonData['content'] !== '') {
                        $updateData['content'] = $jsonData['content'];
                        Log::info('Content from JSON will be updated:', ['new_content' => $jsonData['content']]);
                    }
                    
                    if (isset($jsonData['status']) && $jsonData['status'] !== null && $jsonData['status'] !== '') {
                        $updateData['status'] = $jsonData['status'];
                        Log::info('Status from JSON will be updated:', ['new_status' => $jsonData['status']]);
                    }
                }
            }
            
            // Nếu không có dữ liệu từ JSON, thử lấy từ FormData
            if (empty($updateData)) {
                Log::info('No JSON data, trying FormData...');
                
                if ($request->has('title') && $request->input('title') !== null && $request->input('title') !== '') {
                    $updateData['title'] = $request->input('title');
                    Log::info('Title from FormData will be updated:', ['new_title' => $request->input('title')]);
                }
                
                if ($request->has('content') && $request->input('content') !== null && $request->input('content') !== '') {
                    $updateData['content'] = $request->input('content');
                    Log::info('Content from FormData will be updated:', ['new_content' => $request->input('content')]);
                }
                
                if ($request->has('status') && $request->input('status') !== null && $request->input('status') !== '') {
                    $updateData['status'] = $request->input('status');
                    Log::info('Status from FormData will be updated:', ['new_status' => $request->input('status')]);
                }
            }
            
            // Xử lý ảnh nếu có
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có
                if ($post->image) {
                    Storage::delete($post->image);
                    Log::info('Old image deleted:', ['old_image' => $post->image]);
                }
                $updateData['image'] = $request->file('image')->store('posts', 'public');
                Log::info('New image stored:', ['new_image' => $updateData['image']]);
            }
            
            // Log dữ liệu sẽ được cập nhật
            Log::info('Data to be updated:', $updateData);
            
            // Kiểm tra xem có dữ liệu để cập nhật không
            if (empty($updateData)) {
                Log::warning('No data to update');
                return response()->json([
                    'success' => false,
                    'message' => 'Không có dữ liệu nào để cập nhật'
                ], 400);
            }
            
            // Cập nhật bài viết
            $updateResult = $post->update($updateData);
            Log::info('Update result:', [
                'update_result' => $updateResult,
                'post_after_update' => $post->toArray()
            ]);
            
            // Refresh model để lấy dữ liệu mới nhất
            $post->refresh();
            Log::info('Post after refresh:', $post->toArray());
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật bài viết thành công',
                'data' => new PostResource($post)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Post update error:', [
                'post_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật bài viết: ' . $e->getMessage()
            ], 500);
        }
    }

    // Xóa bài viết
    public function destroy($id)
    {
        try {
            // Log dữ liệu đầu vào
            Log::info('Post destroy request:', [
                'post_id' => $id
            ]);
            
            $post = Post::findOrFail($id);
            
            // Log thông tin bài viết trước khi xóa
            Log::info('Post to be deleted:', [
                'post_id' => $post->id,
                'title' => $post->title,
                'has_image' => !empty($post->image)
            ]);
            
            if ($post->image) {
                Storage::delete($post->image);
                Log::info('Post image deleted:', ['image_path' => $post->image]);
            }
            
            $deleteResult = $post->delete();
            Log::info('Post delete result:', [
                'delete_result' => $deleteResult,
                'post_id' => $id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa bài viết thành công'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Post destroy error:', [
                'post_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa bài viết: ' . $e->getMessage()
            ], 500);
        }
    }

    // Chuyển đổi trạng thái bài viết
    public function toggleStatus($id)
    {
        try {
            // Log dữ liệu đầu vào
            Log::info('Post toggle status request:', [
                'post_id' => $id
            ]);
            
            $post = Post::findOrFail($id);
            
            // Log thông tin bài viết trước khi thay đổi
            Log::info('Post before status change:', [
                'post_id' => $post->id,
                'current_status' => $post->status
            ]);
            
            $oldStatus = $post->status;
            $newStatus = $post->status === 'public' ? 'draft' : 'public';
            
            Log::info('Status change details:', [
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            $post->status = $newStatus;
            $saveResult = $post->save();
            
            Log::info('Post status change result:', [
                'save_result' => $saveResult,
                'post_after_change' => $post->toArray()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Chuyển đổi trạng thái bài viết thành công',
                'data' => [
                    'post' => new PostResource($post),
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Post toggle status error:', [
                'post_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi chuyển đổi trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }
} 