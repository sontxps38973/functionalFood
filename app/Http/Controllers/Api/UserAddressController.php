<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Http\Resources\UserAddressResource;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Nếu là admin và có user_id, xem địa chỉ của user bất kỳ
        if (Auth::user() && in_array(Auth::user()->role ?? '', ['admin', 'super_admin']) && $request->has('user_id')) {
            $addresses = UserAddress::where('user_id', $request->user_id)->get();
        } else {
            // User thường chỉ xem địa chỉ của mình
            $addresses = UserAddress::where('user_id', Auth::id())->get();
        }
        return UserAddressResource::collection($addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\\p{L} ]+$/u'],
            'phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'address' => ['required', 'string', 'max:500'],
            'is_default' => 'boolean',
        ]);
        $data['user_id'] = Auth::id();
        // Nếu là địa chỉ mặc định, bỏ mặc định các địa chỉ khác
        if (!empty($data['is_default'])) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }
        $address = UserAddress::create($data);
        return new UserAddressResource($address);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        if (Auth::user() && in_array(Auth::user()->role ?? '', ['admin', 'super_admin']) && $request->has('user_id')) {
            $address = UserAddress::where('id', $id)->where('user_id', $request->user_id)->firstOrFail();
        } else {
            $address = UserAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        }
        return new UserAddressResource($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $address = UserAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'regex:/^[\\p{L} ]+$/u'],
            'phone' => ['sometimes', 'regex:/^0[0-9]{9}$/'],
            'address' => ['sometimes', 'string', 'max:500'],
            'is_default' => 'boolean',
        ]);
        if (!empty($data['is_default'])) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }
        $address->update($data);
        return new UserAddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $address = UserAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $address->delete();
        // Nếu vừa xóa là địa chỉ mặc định, set địa chỉ còn lại (nếu có) thành mặc định
        if ($address->is_default) {
            $other = UserAddress::where('user_id', Auth::id())->first();
            if ($other) {
                $other->is_default = true;
                $other->save();
            }
        }
        return response()->json(['message' => 'Xóa địa chỉ thành công.']);
    }
}
