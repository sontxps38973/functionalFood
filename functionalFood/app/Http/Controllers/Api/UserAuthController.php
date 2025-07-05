<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Admin;

class UserAuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('user-token')->plainTextToken,
        ]);
    }
    public function login(LoginRequest $request)
{
    $credentials = $request->validated();

    // Tìm user theo email
    $user = User::where('email', $credentials['email'])->first();

    // Kiểm tra password
    if (! $user) {
        return response()->json([
            'message' => 'Email hoặc mật khẩu không đúng!'
        ], 401);
    }

    if (! Hash::check($credentials['password'], $user->password)) {
        return response()->json([
            'message' => 'Email hoặc mật khẩu không đúng!'
        ], 401);
    }
    // xử lí avatar
    $user->avatar = $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.jpg');

    // Tạo token đăng nhập
    $token = $user->createToken('user-token')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ]);
}

// Cập nhật avatar
public function updateAvatar(Request $request)
{
    $user = $request->user();
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    // Xử lý upload avatar
    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('users', 'public');
        $user->avatar = $avatarPath;
        $user->save();
    }
    // Lấy lại thông tin người dùng sau khi cập nhật
    $user->refresh();
    // Trả về thông tin người dùng đã cập nhật
    return response()->json([
        'message' => 'Cập nhật avatar thành công.',
        'user' => $user,
    ]);
}
public function logout(Request $request)
{

    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Đăng xuất thành công.'
    ]);
}
    public function adminLogin(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Tìm user theo email
        $user = Admin::where('email', $credentials['email'])->first();

        // Kiểm tra password
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email hoặc mật khẩu không đúng!'
            ], 401);
        }

        // Tạo token đăng nhập
        $token = $user->createToken('remember_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }
    public function adminLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đăng xuất thành công.'
        ]);
    }


}

