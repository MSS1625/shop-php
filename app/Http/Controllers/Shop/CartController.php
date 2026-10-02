<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected Cart $cart) {}

    /**
     * صفحه سبد خرید
     */
    public function index(): View
    {
        return view('shop.cart.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    /**
     * افزودن محصول به سبد — پشتیبانی از درخواست Ajax
     */
    public function store(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        abort_unless($product->is_active, 404);

        if (! $product->inStock()) {
            return $this->response($request, false, 'این محصول فعلاً موجود نیست.');
        }

        $quantity = $this->cart->add($product, $validated['quantity'] ?? 1);

        return $this->response(
            $request,
            true,
            'محصول «'.$product->title.'» به سبد اضافه شد.',
            $quantity,
            $this->cart->count(),
            $this->cart->total()
        );
    }

    /**
     * بروزرسانی تعداد یک محصول در سبد
     */
    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->update($product, $validated['quantity']);

        return $this->response(
            $request,
            true,
            'سبد خرید بروزرسانی شد.',
            $validated['quantity'],
            $this->cart->count(),
            $this->cart->total()
        );
    }

    /**
     * حذف محصول از سبد
     */
    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->cart->remove($product->id);

        return $this->response(
            $request,
            true,
            'محصول از سبد حذف شد.',
            0,
            $this->cart->count(),
            $this->cart->total()
        );
    }

    /**
     * پاسخ واحد برای درخواست‌های Ajax و معمولی
     */
    private function response(
        Request $request,
        bool $success,
        string $message,
        ?int $quantity = null,
        ?int $count = null,
        ?int $total = null,
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'quantity' => $quantity,
                'count' => $count,
                'total' => $total,
                'total_fa' => $total !== null ? fa_price($total) : null,
            ]);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
