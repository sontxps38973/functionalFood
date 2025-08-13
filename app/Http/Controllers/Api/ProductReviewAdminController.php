<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;

class ProductReviewAdminController extends Controller
{
    // Lấy danh sách tất cả review, lọc theo trạng thái, sản phẩm, user
    public function index(Request $request)
    {
        $query = ProductReview::with(['user', 'product']);
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        $reviews = $query->orderByDesc('id')->paginate(20);
        return response()->json(['data' => $reviews]);
    }

    // Duyệt hoặc từ chối review bất kỳ
    public function updateStatus(Request $request, $review_id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string',
        ]);
        $review = ProductReview::findOrFail($review_id);
        if ($request->action === 'approve') {
            $review->status = 'approved';
            $review->flagged = false;
        } else {
            $review->status = 'rejected';
            $review->flagged = true;
        }
        $review->admin_note = $request->admin_note;
        $review->save();
        return response()->json(['message' => 'Review status updated.', 'review' => $review]);
    }
} 