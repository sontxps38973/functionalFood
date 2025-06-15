<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
/**
 * @OA\Tag(name="Products")
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Lấy danh sách sản phẩm (có phân trang)",
     *     tags={"Products"},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Thành công")
     * )
     */
public function index(Request $request)
{
    $perPage = $request->query('per_page', 10);
    return ProductResource::collection(
        Product::with(['category'])->paginate($perPage)
    );
}
 /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Tạo sản phẩm mới",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "category_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Đã tạo")
     * )
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $product = Product::create($data);
        return new ProductResource($product);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     summary="Chi tiết sản phẩm",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Thành công")
     * )
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load(['category', 'images', 'variants', 'reviews']));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{id}",
     *     summary="Cập nhật sản phẩm",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Đã cập nhật")
     * )
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $product->update($data);
        return new ProductResource($product);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     summary="Xóa sản phẩm",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Đã xóa")
     * )
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
  /**
     * @OA\Get(
     *     path="/api/v1/products-search",
     *     summary="Tìm kiếm sản phẩm theo tên",
     *     tags={"Products"},
     *     @OA\Parameter(name="query", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Kết quả tìm kiếm")
     * )
     */
 public function search(Request $request)
    {
        $keyword = $request->query('q');
        $products = Product::where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->paginate(10);
        return ProductResource::collection($products);
    }
/**
     * @OA\Get(
     *     path="/api/v1/products-filter",
     *     summary="Lọc sản phẩm nâng cao",
     *     tags={"Products"},
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", enum={"price_asc", "price_desc", "name_asc", "name_desc"})),
     *     @OA\Response(response=200, description="Kết quả lọc")
     * )
     */
    public function filter(Request $request)
    {
        $query = Product::query()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        if ($request->filled('min_views')) {
            $query->where('view_count', '>=', $request->min_views);
        }

        if ($request->filled('min_rating')) {
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->selectRaw('AVG(rating) as avg_rating')
                    ->havingRaw('avg_rating >= ?', [$request->min_rating]);
            });
        }

        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'most_viewed':
                    $query->orderBy('view_count', 'desc');
                    break;
                case 'rating_desc':
                    $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
                    break;
            }
        }

        return ProductResource::collection($query->paginate(10));
    }

}