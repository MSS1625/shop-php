<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CouponController extends Controller
{
    /**
     * لیست کدهای تخفیف
     */
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', [
            'coupon' => new Coupon([
                'type' => Coupon::TYPE_PERCENT,
                'min_order_amount' => 0,
                'is_active' => true,
            ]),
        ]);
    }

    /**
     * ثبت کد تخفیف جدید
     */
    public function store(CouponRequest $request): RedirectResponse
    {
        $coupon = Coupon::create($request->validatedData());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'کد تخفیف «'.$coupon->code.'» با موفقیت ساخته شد.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', [
            'coupon' => $coupon,
        ]);
    }

    /**
     * بروزرسانی کد تخفیف
     */
    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->validatedData());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'کد تخفیف «'.$coupon->code.'» بروزرسانی شد.');
    }

    /**
     * حذف کد تخفیف — سفارش‌های قبلی کد را به‌صورت متن نگه می‌دارند
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $code = $coupon->code;
        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'کد تخفیف «'.$code.'» حذف شد.');
    }
}
