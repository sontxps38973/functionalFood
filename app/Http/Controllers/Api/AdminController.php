<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Lấy danh sách admin
     */
    public function index(Request $request)
    {
        $query = Admin::query();

        // Filter theo role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search theo tên, email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $admins = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'admins' => $admins->items(),
            'pagination' => [
                'current_page' => $admins->currentPage(),
                'last_page' => $admins->lastPage(),
                'per_page' => $admins->perPage(),
                'total' => $admins->total(),
            ]
        ]);
    }

    /**
     * Lấy chi tiết admin
     */
    public function show($id)
    {
        $admin = Admin::findOrFail($id);

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'status' => $admin->status,
                'last_login_at' => $admin->last_login_at,
                'created_at' => $admin->created_at,
                'updated_at' => $admin->updated_at,
            ]
        ]);
    }

    /**
     * Tạo admin mới (chỉ super admin)
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền super admin
        $currentAdmin = $request->user();
        if ($currentAdmin->role !== 'super_admin') {
            return response()->json([
                'message' => 'Bạn không có quyền tạo tài khoản admin mới.'
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,super_admin',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? 'active';

        $admin = Admin::create($data);

        return response()->json([
            'message' => 'Tạo tài khoản admin thành công.',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'status' => $admin->status,
                'created_at' => $admin->created_at,
            ]
        ], 201);
    }

    /**
     * Cập nhật thông tin admin
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $request->user();

        // Kiểm tra quyền
        if ($currentAdmin->role !== 'super_admin' && $currentAdmin->id !== $admin->id) {
            return response()->json([
                'message' => 'Bạn không có quyền cập nhật thông tin admin này.'
            ], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('admins')->ignore($id)],
            'password' => 'sometimes|string|min:6|confirmed',
            'role' => $currentAdmin->role === 'super_admin' ? 'sometimes|in:admin,super_admin' : 'prohibited',
            'status' => $currentAdmin->role === 'super_admin' ? 'sometimes|in:active,inactive' : 'prohibited',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $admin->update($data);

        return response()->json([
            'message' => 'Cập nhật thông tin admin thành công.',
            'admin' => $admin->fresh()
        ]);
    }

    /**
     * Xóa admin
     */
    public function destroy(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $request->user();

        // Kiểm tra quyền super admin
        if ($currentAdmin->role !== 'super_admin') {
            return response()->json([
                'message' => 'Bạn không có quyền xóa admin.'
            ], 403);
        }

        // Không cho phép xóa chính mình
        if ($currentAdmin->id === $admin->id) {
            return response()->json([
                'message' => 'Không thể xóa tài khoản của chính mình.'
            ], 422);
        }

        $admin->delete();

        return response()->json([
            'message' => 'Xóa admin thành công.'
        ]);
    }

    /**
     * Thay đổi mật khẩu
     */
    public function changePassword(Request $request)
    {
        $admin = $request->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($data['current_password'], $admin->password)) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không đúng.'
            ], 422);
        }

        $admin->update([
            'password' => Hash::make($data['new_password'])
        ]);

        return response()->json([
            'message' => 'Đổi mật khẩu thành công.'
        ]);
    }

    /**
     * Cập nhật profile
     */
    public function updateProfile(Request $request)
    {
        $admin = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('admins')->ignore($admin->id)],
        ]);

        $admin->update($data);

        return response()->json([
            'message' => 'Cập nhật profile thành công.',
            'admin' => $admin->fresh()
        ]);
    }

    /**
     * Lấy thông tin profile
     */
    public function getProfile(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'status' => $admin->status,
                'last_login_at' => $admin->last_login_at,
                'created_at' => $admin->created_at,
            ]
        ]);
    }

    /**
     * Thống kê admin
     */
    public function getStats()
    {
        $stats = [
            'total_admins' => Admin::count(),
            'super_admins' => Admin::where('role', 'super_admin')->count(),
            'regular_admins' => Admin::where('role', 'admin')->count(),
            'active_admins' => Admin::where('status', 'active')->count(),
            'inactive_admins' => Admin::where('status', 'inactive')->count(),
            'recent_logins' => Admin::whereNotNull('last_login_at')
                                 ->orderBy('last_login_at', 'desc')
                                 ->limit(5)
                                 ->get(['id', 'name', 'email', 'last_login_at']),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Kích hoạt/vô hiệu hóa admin
     */
    public function toggleStatus(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $request->user();

        // Không cho phép vô hiệu hóa chính mình
        if ($currentAdmin->id === $admin->id) {
            return response()->json([
                'message' => 'Không thể vô hiệu hóa tài khoản của chính mình.'
            ], 422);
        }

        // // Không cho phép vô hiệu hóa super_admin
        // if ($admin->role === 'super_admin') {
        //     return response()->json([
        //         'message' => 'Không thể vô hiệu hóa tài khoản super admin.'
        //     ], 422);
        // }

        $admin->update(['status' => $admin->status === 'active' ? 'inactive' : 'active']);

        return response()->json([
            'message' => $admin->status === 'active' ? 'Kích hoạt admin thành công.' : 'Vô hiệu hóa admin thành công.',
            'status' => $admin->status
        ]);
    }
} 