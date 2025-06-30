<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Lấy danh sách wishlist của user
    public function index(Request $request)
    {
        $user = Auth::user();
        $wishlists = Wishlist::with('product')->where('user_id', $user->id)->get();
        return response()->json(['data' => $wishlists]);
    }

    // Thêm sản phẩm vào wishlist
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);
        return response()->json(['message' => 'Added to wishlist', 'data' => $wishlist], 201);
    }

    // Xóa sản phẩm khỏi wishlist
    public function destroy(Request $request, $product_id)
    {
        $user = Auth::user();
        $deleted = Wishlist::where('user_id', $user->id)->where('product_id', $product_id)->delete();
        if ($deleted) {
            return response()->json(['message' => 'Removed from wishlist']);
        }
        return response()->json(['message' => 'Not found in wishlist'], 404);
    }
} 