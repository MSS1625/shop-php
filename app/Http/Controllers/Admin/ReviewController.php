<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * لیست نظرات + شمارندهٔ تب‌ها — فیلتر وضعیت اختیاری از query string
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        if (! is_string($status) || ! in_array($status, Review::STATUSES, true)) {
            $status = null;
        }

        $reviews = Review::with(['product:id,title,slug', 'user:id,name'])
            ->status($status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'status' => $status,
            'counts' => [
                'all' => Review::count(),
                Review::STATUS_PENDING => Review::where('status', Review::STATUS_PENDING)->count(),
                Review::STATUS_APPROVED => Review::where('status', Review::STATUS_APPROVED)->count(),
                Review::STATUS_REJECTED => Review::where('status', Review::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    /**
     * تأیید نظر — از این پس در صفحهٔ محصول نمایش داده می‌شود
     */
    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => Review::STATUS_APPROVED]);

        return back()->with('success', 'نظر تأیید شد و از این پس در صفحهٔ محصول نمایش داده می‌شود.');
    }

    /**
     * رد نظر — از صفحهٔ محصول حذف می‌شود اما رکورد می‌ماند
     */
    public function reject(Review $review): RedirectResponse
    {
        $review->update(['status' => Review::STATUS_REJECTED]);

        return back()->with('success', 'نظر رد شد و از صفحهٔ محصول حذف می‌شود.');
    }

    /**
     * حذف کامل نظر
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'نظر حذف شد.');
    }
}
