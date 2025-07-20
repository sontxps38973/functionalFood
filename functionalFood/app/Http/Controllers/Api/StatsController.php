<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    // Tổng quan dashboard
    public function overview()
    {
            $today = Carbon::today();
            $weekStart = Carbon::now()->startOfWeek();
            $monthStart = Carbon::now()->startOfMonth();

            return response()->json([
                'total_revenue' => Order::where('status', 'completed')->sum('total'), 
                'orders_today' => Order::whereDate('created_at', $today)->count(),
                'orders_this_week' => Order::whereBetween('created_at', [$weekStart, now()])->count(),
                'orders_this_month' => Order::whereBetween('created_at', [$monthStart, now()])->count(),
                'products_sold' => OrderItem::sum('quantity'),
                'total_users' => User::count(),
            ]);
        }

    // Doanh thu theo khoảng thời gian
    public function revenue(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        try {
            if ($from && $to) {
                $from = Carbon::parse($from)->startOfDay();
                $to = Carbon::parse($to)->endOfDay();
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ngày không hợp lệ. Định dạng đúng: YYYY-MM-DD'
            ], 400);
        }

        // Truy vấn tất cả đơn hàng đã giao trong khoảng thời gian
        $orders = Order::where('status', 'delivered')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->select('id', 'name', 'total', 'created_at')
            ->orderBy('created_at')
            ->get();

        // Gom nhóm theo ngày
        $grouped = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('Y-m-d');
        });

        // Tính doanh thu và định dạng lại dữ liệu trả về
        $result = $grouped->map(function ($orders, $date) {
            return [
                'date' => $date,
                'revenue' => $orders->sum('total'),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'name' => $order->name, 
                        'total' => $order->total,
                        'created_at' => $order->created_at->toDateTimeString()
                    ];
                })->values()
            ];
        })->values();

        return response()->json($result);
    }
    
    // Doanh thu theo day / week / month / year
    public function revenueSummary(Request $request)
    {
        $type = $request->query('type', 'today'); // Mặc định

        $query = Order::where('status', 'delivered');

        // Xử lý khoảng thời gian theo yêu cầu
        if ($type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($type === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($type === 'month') {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($type === 'year') {
            $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
        } else {
            return response()->json(['error' => 'Tham số type không hợp lệ'], 400);
        }

        // Truy vấn và nhóm theo ngày
        $orders = $query->select('id', 'order_number', 'name', 'total', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('Y-m-d');
        });

        $result = $grouped->map(function ($orders, $date) {
            return [
                'date' => $date,
                'revenue' => $orders->sum('total'),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer' => $order->name,
                        'total' => $order->total,
                        'created_at' => $order->created_at->toDateTimeString(),
                    ];
                })->values()
            ];
        })->values();

        return response()->json($result);
    }

    // Đơn hàng theo trạng thái 
    public function ordersByStatus()
    {
        return response()->json([
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'refunded' => Order::where('status', 'refunded')->count(),
        ]);
    }


    // Top sản phẩm bán chạy
    public function topProducts()
    {
        return OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product:id,name,image')
            ->take(10)
            ->get();
    }

    //Top sản phẩm bán chậm
    public function slowProducts()
    {
        $products = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'delivered')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'asc') 
            ->with('product:id,name,image') 
            ->take(10)
            ->get();

        return response()->json($products);
    }


    // Người dùng mới đăng ký 
    public function newUsers(Request $request)
    {
        $type = $request->query('type', 'week');
        $query = User::query();

        if ($type === 'week') {
            $query->where('created_at', '>=', Carbon::now()->subWeek());
        } elseif ($type === 'month') {
            $query->where('created_at', '>=', Carbon::now()->subMonth());
        }

        return response()->json([
            'count' => $query->count(),
            'users' => $query->orderByDesc('created_at')->take(10)->get()
        ]);
    }

    // Doanh thu theo danh mục sản phẩm
    public function revenueByCategory()
    {
        return Category::select('categories.id', 'categories.name')
            ->join('products', 'products.category_id', '=', 'categories.id')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'delivered')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();
    }


    // Top khách hàng mua nhiều nhất
    public function topCustomers()
    {
        return Order::select('user_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total) as total_spent'))
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->with('user:id,name,email') 
            ->take(10)
            ->get();
    }

}
