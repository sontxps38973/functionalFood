<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Lấy danh sách users (cho admin)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter theo hạng thành viên
        if ($request->has('customer_rank_id')) {
            $query->where('customer_rank_id', $request->customer_rank_id);
        }

        // Filter theo ngày đăng ký
        if ($request->has('registered_from')) {
            $query->whereDate('created_at', '>=', $request->registered_from);
        }

        if ($request->has('registered_to')) {
            $query->whereDate('created_at', '<=', $request->registered_to);
        }

        // Filter theo tổng chi tiêu
        if ($request->has('min_total_spent')) {
            $query->whereHas('orders', function ($q) use ($request) {
                $q->where('status', 'paid');
            })->withSum(['orders' => function ($q) {
                $q->where('status', 'paid');
            }], 'total')->having('orders_sum_total', '>=', $request->min_total_spent);
        }

        if ($request->has('max_total_spent')) {
            $query->whereHas('orders', function ($q) use ($request) {
                $q->where('status', 'paid');
            })->withSum(['orders' => function ($q) {
                $q->where('status', 'paid');
            }], 'total')->having('orders_sum_total', '<=', $request->max_total_spent);
        }

        // Search theo tên, email, phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->with(['customerRank', 'orders'])
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    /**
     * Lấy chi tiết user
     */
    public function show($id)
    {
        $user = User::with(['customerRank', 'orders.items.product', 'orders.coupon'])
                   ->findOrFail($id);

        // Tính toán thống kê
        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->where('status', 'paid')->sum('total'),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'completed_orders' => $user->orders()->where('status', 'delivered')->count(),
            'cancelled_orders' => $user->orders()->where('status', 'cancelled')->count(),
            'last_order_date' => $user->orders()->latest()->first()?->created_at,
            'member_since' => $user->created_at,
        ];

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'customer_rank' => $user->customerRank ? [
                    'id' => $user->customerRank->id,
                    'name' => $user->customerRank->name,
                    'min_total_spent' => $user->customerRank->min_total_spent,
                ] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'stats' => $stats,
                'orders' => $user->orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total' => $order->total,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'created_at' => $order->created_at,
                        'items_count' => $order->items->count(),
                    ];
                }),
            ]
        ]);
    }

    /**
     * Cập nhật thông tin user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'phone' => ['sometimes', 'string', Rule::unique('users')->ignore($id)],
            'customer_rank_id' => 'sometimes|integer|exists:customer_ranks,id',
            'status' => 'sometimes|in:active,inactive,suspended',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Cập nhật thông tin user thành công.',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Xóa user (soft delete)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Kiểm tra xem user có đơn hàng không
        if ($user->orders()->exists()) {
            return response()->json([
                'message' => 'Không thể xóa user đã có đơn hàng.',
                'orders_count' => $user->orders()->count()
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Xóa user thành công.'
        ]);
    }

    /**
     * Thống kê users
     */
    public function getStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'users_with_orders' => User::whereHas('orders')->count(),
            'users_without_orders' => User::whereDoesntHave('orders')->count(),
            'top_spenders' => User::withSum(['orders' => function ($q) {
                $q->where('status', 'paid');
            }], 'total')
            ->orderByDesc('orders_sum_total')
            ->limit(10)
            ->get(['id', 'name', 'email', 'orders_sum_total']),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Lấy danh sách customer ranks
     */
    public function getCustomerRanks()
    {
        $ranks = \App\Models\CustomerRank::orderBy('min_total_spent')->get();

        return response()->json(['ranks' => $ranks]);
    }

    /**
     * Export users (CSV/Excel)
     */
    public function export(Request $request)
    {
        $query = User::query();

        // Áp dụng các filter tương tự như index
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('customer_rank_id')) {
            $query->where('customer_rank_id', $request->customer_rank_id);
        }

        $users = $query->with(['customerRank'])
                      ->orderBy('created_at', 'desc')
                      ->get();

        // Format data cho export
        $exportData = $users->map(function ($user) {
            return [
                'ID' => $user->id,
                'Tên' => $user->name,
                'Email' => $user->email,
                'Số điện thoại' => $user->phone,
                'Hạng thành viên' => $user->customerRank?->name ?? 'Chưa có hạng',
                'Trạng thái' => $user->status,
                'Ngày đăng ký' => $user->created_at->format('d/m/Y H:i:s'),
                'Tổng đơn hàng' => $user->orders()->count(),
                'Tổng chi tiêu' => $user->orders()->where('status', 'paid')->sum('total'),
            ];
        });

        return response()->json([
            'message' => 'Dữ liệu export thành công.',
            'data' => $exportData,
            'total' => $exportData->count()
        ]);
    }

    /**
     * Đổi mật khẩu cho user
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
    
        // Validate dữ liệu đầu vào
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
    
        // Kiểm tra mật khẩu hiện tại có đúng không
        if (!Hash::check($request->current_password, (string)$user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng.'], 401);
        }
    
        // Cập nhật mật khẩu mới
        $user->password = $request->new_password;
        $user->save();
    
        return response()->json(['message' => 'Đổi mật khẩu thành công.'], 200);
    }

    /**
     * Admin: Khóa/mở khóa tài khoản user
     */
    public function toggleStatus(Request $request, $id)
    {
        $admin = $request->user();
        if (!$admin || !in_array($admin->role, ['admin', 'super_admin'])) {
            return response()->json([
                'message' => 'Bạn không có quyền thay đổi trạng thái user.'
            ], 403);
        }

        $user = User::findOrFail($id);
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();
        return response()->json([
            'message' => $user->status === 'active' ? 'Kích hoạt user thành công.' : 'Khóa user thành công.',
            'status' => $user->status
        ]);
    }

    /**
     * Cập nhật thông tin người dùng (user tự cập nhật)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'address' => 'required|string|max:500',
        ]);
        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->address = $data['address'];
        $user->save();
        return response()->json([
            'message' => 'Cập nhật thông tin thành công.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => $user->address,
                'email' => $user->email,
                'status' => $user->status,
                'customer_rank_id' => $user->customer_rank_id,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }
} 