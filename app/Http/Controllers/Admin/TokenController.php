<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /** تب‌های فیلتر لیست */
    private const FILTERS = ['all', 'unused', 'expired'];

    /** گزینه‌های مدت اعتبار — کلید همان مقداری است که فرم می‌فرستد */
    private const EXPIRY_KEYS = ['never', '7', '30', '90'];

    /**
     * لیست توکن‌های API کاربران + شمارندهٔ تب‌ها + فرم صدور
     * (فیلتر اختیاری از query string، همان الگوی پرسش‌ها)
     */
    public function index(Request $request): View
    {
        $filter = (string) $request->query('filter', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        // فقط توکن‌های «کاربر» — نه هر توکن Sanctum دیگری
        $base = PersonalAccessToken::query()->where('tokenable_type', User::class);

        $tokens = (clone $base)
            ->when($filter === 'unused', fn (Builder $q) => $q->whereNull('last_used_at'))
            ->when($filter === 'expired', fn (Builder $q) => $q->whereNotNull('expires_at')->where('expires_at', '<', now()))
            ->with('tokenable')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.tokens.index', [
            'tokens' => $tokens,
            'filter' => $filter,
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'is_admin']),
            'counts' => [
                'all' => (clone $base)->count(),
                'unused' => (clone $base)->whereNull('last_used_at')->count(),
                'expired' => (clone $base)->whereNotNull('expires_at')->where('expires_at', '<', now())->count(),
            ],
        ]);
    }

    /**
     * صدور توکن از طرف پنل — برای راه‌اندازی اپ/دستگاه یک کاربر.
     * مقدار کامل فقط یک‌بار، بعد از ریدایرکت، در مودال نمایش داده می‌شود؛
     * در دیتابیس فقط هشِ آن ذخیره می‌شود (سیاست خود Sanctum).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'expires' => ['required', 'string', Rule::in(self::EXPIRY_KEYS)],
        ], [
            'user_id.required' => 'انتخاب کاربر الزامی است.',
            'user_id.integer' => 'کاربر انتخاب‌شده نامعتبر است.',
            'user_id.exists' => 'کاربر انتخاب‌شده یافت نشد.',
            'name.required' => 'نام توکن الزامی است.',
            'name.min' => 'نام توکن باید حداقل :min کاراکتر باشد.',
            'name.max' => 'نام توکن حداکثر می‌تواند :max کاراکتر باشد.',
            'expires.required' => 'انتخاب مدت اعتبار الزامی است.',
            'expires.in' => 'مدت اعتبار نامعتبر است.',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $name = trim($validated['name']);

        $plain = $user
            ->createToken($name, ['*'], $this->expiryAt($validated['expires']))
            ->plainTextToken;

        return redirect()
            ->route('admin.tokens.index')
            ->with('success', 'توکن صادر شد؛ مقدار آن را از مودال کپی کنید — دوباره نمایش داده نمی‌شود.')
            ->with('new_token', $plain)
            ->with('new_token_name', $name);
    }

    /**
     * ابطال توکن — دستگاه مربوطه باید دوباره وارد شود؛
     * سفارش‌ها، نظرات و بقیهٔ داده‌های کاربر دست‌نخورده می‌مانند.
     */
    public function destroy(PersonalAccessToken $token): RedirectResponse
    {
        $token->delete();

        return back()->with('success', 'توکن باطل شد؛ دیگر قابل استفاده نیست.');
    }

    /**
     * «never» → بدون انقضا؛ عدد → تاریخ انقضای واقعی که
     * Sanctum هنگام احراز هویت بررسی می‌کند و توکن منقضی را رد می‌کند.
     */
    private function expiryAt(string $key): ?DateTimeInterface
    {
        return match ($key) {
            '7' => now()->addDays(7),
            '30' => now()->addDays(30),
            '90' => now()->addDays(90),
            default => null,
        };
    }
}
