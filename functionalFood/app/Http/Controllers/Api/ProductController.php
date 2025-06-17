<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductView;
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
     *          @OA\Property(property="discount", type="number", nullable=true),
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

    // Tạo sản phẩm (tạm thời chưa có ảnh chính)
    $product = Product::create($data);

    // Lưu ảnh phụ và gán ảnh đầu tiên làm ảnh chính
    if ($request->hasFile('images')) {
        $isFirst = true;
        foreach ($request->file('images') as $file) {
            $path = $file->store('public/products');
            $imageUrl = Storage::url($path);

            // Lưu vào bảng product_images
            $product->images()->create([
                'image' => $imageUrl,
            ]);

            // Ảnh đầu tiên sẽ là ảnh chính
            if ($isFirst) {
                $product->image = $imageUrl;
                $product->save();
                $isFirst = false;
            }
        }
    }

    // Lưu biến thể (nếu có)
    if ($request->has('variants')) {
        foreach ($request->input('variants') as $index => $variantData) {
            $variant = $product->variants()->create([
                'name'         => $variantData['name'],
                'price'        => $variantData['price'],
                'stock'        => $variantData['stock'] ?? 0,
                'sku'          => $variantData['sku'] ?? null,
            ]);

            if ($request->hasFile("variants.$index.image")) {
                $variantImage = $request->file("variants.$index.image")->store('public/variants');
                $variant->image = Storage::url($variantImage);
                $variant->save();
            }
        }
    }

    return new ProductResource($product->load(['images', 'variants']));
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
    $userId = Auth::check() ? Auth::id() : null;
    $ip = Request::ip();

    // Tạo key cache duy nhất theo user hoặc IP
    $cacheKey = 'viewed_product_' . $product->id . '_' . ($userId ?? $ip);

    // Kiểm tra nếu đã xem trong 24h
    if (!Cache::has($cacheKey)) {
        // Tăng view và lưu lịch sử
        ProductView::create([
            'product_id' => $product->id,
            'user_id'    => $userId,
            'ip_address' => $ip,
        ]);

        // Set cache tồn tại 24h
        Cache::put($cacheKey, true, now()->addHours(24));
    }

    // Trả về chi tiết sản phẩm (kèm category, variants, reviews...)
    return new ProductResource(
        $product->load(['category', 'images', 'variants', 'reviews'])
    );
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
    DB::beginTransaction();
    try {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        // Cập nhật thông tin cơ bản sản phẩm
        $product->update($data);

        // Xử lý ảnh sản phẩm
        if ($request->hasFile('images')) {
            // Xóa ảnh cũ
            $product->images()->delete();

            foreach ($request->file('images') as $index => $imageFile) {
                $path = $imageFile->store('products', 'public');

                $product->images()->create([
                    'image_path' => $path
                ]);

                // Cập nhật ảnh đại diện là ảnh đầu tiên
                if ($index === 0) {
                    $product->image = $path;
                    $product->save();
                }
            }
        }

        // Xử lý biến thể
        if ($request->has('variants')) {
            // Xóa biến thể cũ
            $product->variants()->delete();

            foreach ($request->variants as $variantData) {
                $variant = $product->variants()->create([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock'] ?? 0,
                ]);

                // Lưu ảnh biến thể nếu có
                if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $variantPath = $variantData['image']->store('variants', 'public');
                    $variant->update(['image' => $variantPath]);
                }
            }
        }

        DB::commit();
        return new ProductResource($product->fresh([
            'category', 'images', 'variants', 'reviews', 'views'
        ]));

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Lỗi khi cập nhật sản phẩm.',
            'error' => $e->getMessage(),
        ], 500);
    }
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