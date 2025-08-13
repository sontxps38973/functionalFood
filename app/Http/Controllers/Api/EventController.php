<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventProduct;
use App\Models\Product;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventProductResource;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    // Danh sách sự kiện public (chỉ events đang hoạt động)
    public function publicIndex(Request $request)
    {
        $query = Event::query()->where('status', 'active');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }
        
        if ($request->has('type')) {
            switch ($request->type) {
                case 'running':
                    $query->running();
                    break;
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'ended':
                    $query->ended();
                    break;
                default:
                    // Mặc định là active
                    break;
            }
        }
        
        $query->orderBy('sort_order')->orderByDesc('start_time');
        $events = $query->with(['eventProducts.product'])->paginate($request->get('per_page', 15));
        
        return EventResource::collection($events);
    }
    
    // Xem chi tiết sự kiện public
    public function publicShow($id)
    {
        $event = Event::where('status', 'active')
            ->with(['eventProducts.product'])
            ->findOrFail($id);
            
        return new EventResource($event);
    }

    // Danh sách sự kiện (lọc, tìm kiếm, phân trang) - Admin only
    public function index(Request $request)
    {
        $query = Event::query();
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }
        $query->orderBy('sort_order')->orderByDesc('start_time');
        $events = $query->with('eventProducts')->paginate($request->get('per_page', 15));
        return EventResource::collection($events);
    }

    // Tạo sự kiện
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'in:draft,active,paused,ended',
            'banner_image' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);
        $event = Event::create($data);
        return new EventResource($event->load('eventProducts'));
    }

    // Xem chi tiết sự kiện
    public function show($id)
    {
        $event = Event::with('eventProducts.product')->findOrFail($id);
        return new EventResource($event);
    }

    // Cập nhật sự kiện
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'status' => 'in:draft,active,paused,ended',
            'banner_image' => 'nullable|string',
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);
        $event->update($data);
        return new EventResource($event->fresh('eventProducts'));
    }

    // Xóa sự kiện
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return response()->json(['message' => 'Xóa sự kiện thành công.']);
    }

    // Đổi trạng thái sự kiện
    public function changeStatus(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $data = $request->validate([
            'status' => 'required|in:draft,active,paused,ended',
        ]);
        $event->update(['status' => $data['status']]);
        return response()->json(['message' => 'Cập nhật trạng thái thành công.', 'status' => $event->status]);
    }

    // Lấy danh sách sản phẩm trong sự kiện
    public function products($eventId, Request $request)
    {
        $event = Event::findOrFail($eventId);
        $query = $event->eventProducts()->with('product');
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $products = $query->paginate($request->get('per_page', 20));
        return EventProductResource::collection($products);
    }

    // Thêm sản phẩm vào sự kiện
    public function addProduct(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'event_price' => 'required|numeric|min:0',
            'original_price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'quantity_limit' => 'required|integer|min:0',
            'status' => 'in:active,inactive,sold_out',
            'sort_order' => 'integer',
        ]);
        $data['event_id'] = $event->id;
        $eventProduct = EventProduct::create($data);
        return new EventProductResource($eventProduct->load('product'));
    }

    // Cập nhật sản phẩm trong sự kiện
    public function updateProduct(Request $request, $eventId, $eventProductId)
    {
        $event = Event::findOrFail($eventId);
        $eventProduct = $event->eventProducts()->findOrFail($eventProductId);
        $data = $request->validate([
            'event_price' => 'sometimes|numeric|min:0',
            'original_price' => 'sometimes|numeric|min:0',
            'discount_price' => 'sometimes|numeric|min:0',
            'quantity_limit' => 'sometimes|integer|min:0',
            'status' => 'in:active,inactive,sold_out',
            'sort_order' => 'integer',
        ]);
        $eventProduct->update($data);
        return new EventProductResource($eventProduct->fresh('product'));
    }

    // Xóa sản phẩm khỏi sự kiện
    public function removeProduct($eventId, $eventProductId)
    {
        $event = Event::findOrFail($eventId);
        $eventProduct = $event->eventProducts()->findOrFail($eventProductId);
        $eventProduct->delete();
        return response()->json(['message' => 'Xóa sản phẩm khỏi sự kiện thành công.']);
    }
}
