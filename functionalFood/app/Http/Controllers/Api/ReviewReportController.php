<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReviewReport;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Auth;

class ReviewReportController extends Controller
{
    // User báo cáo review
    public function report(Request $request, $review_id)
    {
        $user = Auth::user();
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        $review = ProductReview::findOrFail($review_id);
        // Không cho phép báo cáo nhiều lần cùng 1 review bởi cùng 1 user
        if (ReviewReport::where('review_id', $review_id)->where('reporter_id', $user->id)->exists()) {
            return response()->json(['message' => 'You have already reported this review.'], 409);
        }
        $report = ReviewReport::create([
            'review_id' => $review_id,
            'reporter_id' => $user->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);
        return response()->json(['message' => 'Report submitted. Admin will review this soon.', 'data' => $report], 201);
    }

    // Admin: xem danh sách report (có thể lọc theo trạng thái)
    public function index(Request $request)
    {
        $query = ReviewReport::with(['review', 'reporter']);
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $reports = $query->orderByDesc('id')->paginate(20);
        return response()->json(['data' => $reports]);
    }

    // Admin: xử lý report (approve hoặc reject review)
    public function resolve(Request $request, $report_id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string',
        ]);
        $report = ReviewReport::with('review')->findOrFail($report_id);
        $review = $report->review;
        if ($request->action === 'approve') {
            $review->status = 'approved';
            $review->flagged = false;
        } else {
            $review->status = 'rejected';
            $review->flagged = true;
        }
        $review->admin_note = $request->admin_note;
        $review->save();
        $report->status = 'resolved';
        $report->admin_note = $request->admin_note;
        $report->save();
        return response()->json(['message' => 'Report resolved.', 'review' => $review, 'report' => $report]);
    }
} 