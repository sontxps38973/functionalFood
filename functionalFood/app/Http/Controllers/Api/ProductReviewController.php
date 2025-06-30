<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    // Lấy danh sách đánh giá đã duyệt của sản phẩm
    public function index($product_id)
    {
        $reviews = ProductReview::with('user')
            ->where('product_id', $product_id)
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->paginate(10);
        return response()->json(['data' => $reviews]);
    }

    // Gửi đánh giá sản phẩm
    public function store(Request $request, $product_id)
    {
        $user = Auth::user();
        // Kiểm tra đã mua hàng
        $hasBought = OrderItem::whereHas('order', function($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'completed');
        })->where('product_id', $product_id)->exists();
        if (!$hasBought) {
            return response()->json(['message' => 'You can only review products you have purchased.'], 403);
        }
        // Kiểm tra đã từng đánh giá
        if (ProductReview::where('user_id', $user->id)->where('product_id', $product_id)->exists()) {
            return response()->json(['message' => 'You have already reviewed this product.'], 409);
        }
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);
        // Kiểm duyệt từ khóa cấm
        $blacklist = ['badword1', 'badword2', 'http://', 'https://', 'spamword'];
        $comment = strtolower($request->input('comment', ''));
        $isFlagged = false;
        foreach ($blacklist as $word) {
            if (strpos($comment, $word) !== false) {
                $isFlagged = true;
                break;
            }
        }
        $status = $isFlagged ? 'rejected' : 'approved';
        $review = ProductReview::create([
            'user_id' => $user->id,
            'product_id' => $product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => $status,
            'flagged' => $isFlagged,
        ]);
        return response()->json(['message' => $isFlagged ? 'Your review contains inappropriate content and was rejected.' : 'Review submitted successfully.', 'data' => $review], $isFlagged ? 422 : 201);
    }

    // Sửa đánh giá của mình
    public function update(Request $request, $product_id, $review_id)
    {
        $user = Auth::user();
        $review = ProductReview::where('id', $review_id)->where('user_id', $user->id)->where('product_id', $product_id)->firstOrFail();
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);
        // Kiểm duyệt lại từ khóa cấm
        $blacklist = ['badword1', 'badword2', 'http://', 'https://', 'spamword'];
        $comment = strtolower($request->input('comment', ''));
        $isFlagged = false;
        foreach ($blacklist as $word) {
            if (strpos($comment, $word) !== false) {
                $isFlagged = true;
                break;
            }
        }
        $status = $isFlagged ? 'rejected' : 'approved';
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => $status,
            'flagged' => $isFlagged,
        ]);
        return response()->json(['message' => $isFlagged ? 'Your review contains inappropriate content and was rejected.' : 'Review updated successfully.', 'data' => $review]);
    }

    // Xóa đánh giá của mình
    public function destroy($product_id, $review_id)
    {
        $user = Auth::user();
        $review = ProductReview::where('id', $review_id)->where('user_id', $user->id)->where('product_id', $product_id)->firstOrFail();
        $review->delete();
        return response()->json(['message' => 'Review deleted.']);
    }
} 