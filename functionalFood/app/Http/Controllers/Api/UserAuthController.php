<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        return response()->json([
            'message' => 'Email hoặc mật khẩu không đúng!'
        ], 401);
    }

    // Tạo token đăng nhập
    $token = $user->createToken('user-token')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ]);
}
public function logout(Request $request)
{

    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Đăng xuất thành công.'
    ]);
}


}

