<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Str;

/**
 * @OA\Tag(name="Categories")
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Lấy danh sách danh mục",
     *     tags={"Categories"},
     *     @OA\Response(response=200, description="Thành công")
     * )
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }
    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     summary="Tạo danh mục mới",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Đã tạo")
     * )
     */
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $category = Category::create($data);
        return new CategoryResource($category);
    }
/**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     summary="Lấy chi tiết danh mục",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Thành công")
     * )
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }
/**
     * @OA\Put(
     *     path="/api/v1/categories/{id}",
     *     summary="Cập nhật danh mục",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Đã cập nhật")
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);
        return new CategoryResource($category);
    }
/**
     * @OA\Delete(
     *     path="/api/v1/categories/{id}",
     *     summary="Xóa danh mục",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Đã xóa")
     * )
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
