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
use Illuminate\Support\Facades\Log;
use App\Models\ProductView;

class ProductController extends Controller
{

public function index(Request $request)
{
    $perPage = $request->query('per_page', 10);
    $products = Product::with(['category', 'eventProducts.event'])->paginate($perPage);
    
    // Transform products để bao gồm thông tin giá cả sự kiện
    $transformedProducts = $products->getCollection()->map(function ($product) {
        $eventPriceInfo = $product->getBestEventPrice();
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'status' => $product->status,
            'product_type' => $product->product_type,
            'category' => new \App\Http\Resources\CategoryResource($product->category),
            
            // Giá cả cơ bản
            'base_price' => $product->price,
            'base_discount' => $product->discount,
            
            // Thông tin sự kiện
            'event_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : null,
            'original_price' => $eventPriceInfo ? $eventPriceInfo['original_price'] : null,
            'event_info' => $eventPriceInfo ? $eventPriceInfo['event_info'] : null,
            
            // Giá hiển thị (ưu tiên sự kiện)
            'display_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : $product->price,
            'display_discount_price' => $eventPriceInfo ? $eventPriceInfo['discount_price'] : $product->discount,
            
            'stock_quantity' => $product->stock_quantity,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            
            // Thông tin bổ sung về sự kiện
            'has_active_event' => $eventPriceInfo !== null,
            'event_discount_percentage' => $eventPriceInfo ? 
                round((($eventPriceInfo['original_price'] - $eventPriceInfo['event_price']) / $eventPriceInfo['original_price']) * 100, 1) : null,
        ];
    });
    
    // Tạo pagination response mới
    $products->setCollection($transformedProducts);
    
    return response()->json([
        'success' => true,
        'data' => $products->items(),
        'links' => $products->links(),
        'meta' => [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]
    ]);
}

public function store(StoreProductRequest $request)
{
    $data = $request->validated();
    $data['slug'] = Str::slug($data['name']);

    // Tạo sản phẩm
    $product = Product::create($data);

    // Lưu ảnh sản phẩm (ảnh chính + ảnh phụ)
    if ($request->hasFile('images')) {
        $isFirst = true;
        foreach ($request->file('images') as $file) {
            $path = $file->store('products', 'public');
            $product->images()->create([
                'image_path' => $path,
                'alt_text'   => $product->name,
                'is_main'    => $isFirst,
            ]);
            // Gán ảnh đầu tiên làm ảnh đại diện cho bảng products
            if ($isFirst) {
                $product->image = $path;
                $product->save();
                $isFirst = false;
            }
        }
    }

    // Lưu biến thể nếu có
    if ($request->has('variants')) {
        // Lấy tất cả index của variants từ FormData
        $variantIndexes = array_keys($request->input('variants'));
        foreach ($variantIndexes as $index) {
            $sku = $request->input("variants.$index.sku");
            $name = $request->input("variants.$index.name");
            $price = $request->input("variants.$index.price");
            $stock_quantity = $request->input("variants.$index.stock_quantity");
            $discount = $request->input("variants.$index.discount") ?? 0;
            $attributes = $request->input("variants.$index.attributes") ?? [];
            // Tạo biến thể
            $variant = $product->variants()->create([
                'sku'             => $sku,
                'name'            => $name,
                'attribute_json'  => is_array($attributes) ? json_encode($attributes) : $attributes,
                'price'           => $price,
                'discount'        => $discount,
                'stock_quantity'  => $stock_quantity ?? 0,
            ]);
            // Ảnh biến thể nếu có
            if ($request->hasFile("variants.$index.image")) {
                $variantImage = $request->file("variants.$index.image")->store('variants', 'public');
                $variant->image = $variantImage;
                $variant->save();
            }
        }
    }

    return new ProductResource($product->load(['images', 'variants']));
}

public function show(Product $product)
{
    $userId = Auth::check() ? Auth::id() : null;
    $ip = request()->ip();

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
    
    // Trả về chi tiết sản phẩm với thông tin giá cả sự kiện
    $product->load(['category', 'images', 'variants', 'reviews', 'relatedProducts', 'eventProducts.event']);
    
    // Lấy thông tin giá cả sự kiện
    $eventPriceInfo = $product->getBestEventPrice();

    // Tạo response tùy chỉnh
    $response = [
        'id' => $product->id,
        'name' => $product->name,
        'slug' => $product->slug,
        'description' => $product->description,
        'status' => $product->status,
        'product_type' => $product->product_type,
        'category' => new \App\Http\Resources\CategoryResource($product->category),
        
        // Giá cả cơ bản
        'base_price' => $product->price,
        'base_discount' => $product->discount,
        
        // Thông tin sự kiện
        'event_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : null,
        'original_price' => $eventPriceInfo ? $eventPriceInfo['original_price'] : null,
        'event_info' => $eventPriceInfo ? $eventPriceInfo['event_info'] : null,
        
        // Giá hiển thị (ưu tiên sự kiện)
        'display_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : $product->price,
        'display_discount_price' => $eventPriceInfo ? $eventPriceInfo['discount_price'] : $product->discount,
        
        'stock_quantity' => $product->stock_quantity,
        'image' => $product->image ? asset('storage/' . $product->image) : null,
        
        'images' => \App\Http\Resources\ProductImageResource::collection($product->images),
        'variants' => \App\Http\Resources\ProductVariantResource::collection($product->variants),
        'reviews' => \App\Http\Resources\ProductReviewResource::collection($product->reviews),
        'related_products' => \App\Http\Resources\ProductResource::collection($product->relatedProducts),
        
        'average_rating' => round($product->reviews->avg('rating'), 1),
        'views_count' => $product->views()->count(),
        
        // Thông tin bổ sung về sự kiện
        'has_active_event' => $eventPriceInfo !== null,
        'event_discount_percentage' => $eventPriceInfo ? 
            round((($eventPriceInfo['original_price'] - $eventPriceInfo['event_price']) / $eventPriceInfo['original_price']) * 100, 1) : null,
    ];

    return response()->json([
        'success' => true,
        'data' => $response
    ]);
}




    public function update(UpdateProductRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            
            $data = $request->validated();
            $data['slug'] = Str::slug($data['name']);

            // Đảm bảo status là boolean
            if (isset($data['status'])) {
                $data['status'] = (int) $data['status'];
            }

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

                foreach ($request->input('variants', []) as $index => $variantData) {
                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'] ?? null,
                        'name' => $variantData['name'] ?? null,
                        'price' => $variantData['price'] ?? 0,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'discount' => $variantData['discount'] ?? 0,
                        'attribute_json' => isset($variantData['attributes']) ? json_encode($variantData['attributes']) : '{}',
                    ]);

                    // Lưu ảnh biến thể nếu có
                    if ($request->hasFile("variants.$index.image")) {
                        $variantImage = $request->file("variants.$index.image")->store('variants', 'public');
                        $variant->image = $variantImage;
                        $variant->save();
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


    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

  public function search(Request $request)
     {
         $keyword = $request->query('q');
         $products = Product::with(['category', 'eventProducts.event'])
             ->where('name', 'like', "%{$keyword}%")
             ->orWhere('description', 'like', "%{$keyword}%")
             ->paginate(10);
         
         // Transform products để bao gồm thông tin giá cả sự kiện
         $transformedProducts = $products->getCollection()->map(function ($product) {
             $eventPriceInfo = $product->getBestEventPrice();
             
             return [
                 'id' => $product->id,
                 'name' => $product->name,
                 'slug' => $product->slug,
                 'description' => $product->description,
                 'status' => $product->status,
                 'product_type' => $product->product_type,
                 'category' => new \App\Http\Resources\CategoryResource($product->category),
                 
                 // Giá cả cơ bản
                 'base_price' => $product->price,
                 'base_discount' => $product->discount,
                 
                 // Thông tin sự kiện
                 'event_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : null,
                 'original_price' => $eventPriceInfo ? $eventPriceInfo['original_price'] : null,
                 'event_info' => $eventPriceInfo ? $eventPriceInfo['event_info'] : null,
                 
                 // Giá hiển thị (ưu tiên sự kiện)
                 'display_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : $product->price,
                 'display_discount_price' => $eventPriceInfo ? $eventPriceInfo['discount_price'] : $product->discount,
                 
                 'stock_quantity' => $product->stock_quantity,
                 'image' => $product->image ? asset('storage/' . $product->image) : null,
                 
                 // Thông tin bổ sung về sự kiện
                 'has_active_event' => $eventPriceInfo !== null,
                 'event_discount_percentage' => $eventPriceInfo ? 
                     round((($eventPriceInfo['original_price'] - $eventPriceInfo['event_price']) / $eventPriceInfo['original_price']) * 100, 1) : null,
             ];
         });
         
         // Tạo pagination response mới
         $products->setCollection($transformedProducts);
         
         return response()->json([
             'success' => true,
             'data' => $products->items(),
             'links' => $products->links(),
             'meta' => [
                 'current_page' => $products->currentPage(),
                 'last_page' => $products->lastPage(),
                 'per_page' => $products->perPage(),
                 'total' => $products->total(),
             ]
         ]);
     }

         public function filter(Request $request)
     {
         $query = Product::query()->with(['category', 'eventProducts.event']);

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

         $products = $query->paginate(10);
         
         // Transform products để bao gồm thông tin giá cả sự kiện
         $transformedProducts = $products->getCollection()->map(function ($product) {
             $eventPriceInfo = $product->getBestEventPrice();
             
             return [
                 'id' => $product->id,
                 'name' => $product->name,
                 'slug' => $product->slug,
                 'description' => $product->description,
                 'status' => $product->status,
                 'product_type' => $product->product_type,
                 'category' => new \App\Http\Resources\CategoryResource($product->category),
                 
                 // Giá cả cơ bản
                 'base_price' => $product->price,
                 'base_discount' => $product->discount,
                 
                 // Thông tin sự kiện
                 'event_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : null,
                 'original_price' => $eventPriceInfo ? $eventPriceInfo['original_price'] : null,
                 'event_info' => $eventPriceInfo ? $eventPriceInfo['event_info'] : null,
                 
                 // Giá hiển thị (ưu tiên sự kiện)
                 'display_price' => $eventPriceInfo ? $eventPriceInfo['event_price'] : $product->price,
                 'display_discount_price' => $eventPriceInfo ? $eventPriceInfo['discount_price'] : $product->discount,
                 
                 'stock_quantity' => $product->stock_quantity,
                 'image' => $product->image ? asset('storage/' . $product->image) : null,
                 
                 // Thông tin bổ sung về sự kiện
                 'has_active_event' => $eventPriceInfo !== null,
                 'event_discount_percentage' => $eventPriceInfo ? 
                     round((($eventPriceInfo['original_price'] - $eventPriceInfo['event_price']) / $eventPriceInfo['original_price']) * 100, 1) : null,
             ];
         });
         
         // Tạo pagination response mới
         $products->setCollection($transformedProducts);
         
         return response()->json([
             'success' => true,
             'data' => $products->items(),
             'links' => $products->links(),
             'meta' => [
                 'current_page' => $products->currentPage(),
                 'last_page' => $products->lastPage(),
                 'per_page' => $products->perPage(),
                 'total' => $products->total(),
             ]
         ]);
     }

}