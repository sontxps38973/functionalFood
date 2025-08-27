<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
            
            // Kiểm tra dữ liệu trước khi trả về
            if ($categories->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Không có danh mục nào',
                    'data' => []
                ], 200);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách danh mục thành công',
                'data' => CategoryResource::collection($categories)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Category index error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['slug'] = Str::slug($data['name']);
            
            $category = Category::create($data);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo danh mục thành công',
                'data' => new CategoryResource($category)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Category store error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin danh mục thành công',
                'data' => new CategoryResource($category)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Category show error:', [
                'category_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // Tìm category theo ID
            $category = Category::findOrFail($id);
            
            $data = $request->validated();
            
            // Kiểm tra xem tên có thay đổi không
            if ($data['name'] !== $category->name) {
                $data['slug'] = Str::slug($data['name']);
            }
            
            // Cập nhật dữ liệu
            $category->update($data);
            
            // Refresh model để đảm bảo dữ liệu mới nhất
            $category->refresh();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công',
                'data' => new CategoryResource($category)
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Category update error:', [
                'category_id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $category = Category::findOrFail($id);
            
            // Kiểm tra xem danh mục có sản phẩm nào không
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục đang có sản phẩm',
                    'error' => 'Category has products'
                ], 422);
            }
            
            $category->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Category destroy error:', [
                'category_id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
