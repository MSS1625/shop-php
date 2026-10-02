<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * محدودیت تلاش ناموفق: ۵ تلاش در هر دقیقه
     */
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * ورود امن مدیر با محدودیت نرخ (ضد Brute Force)
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل معتبر نیست.',
            'password.required' => 'رمز عبور الزامی است.',
        ]);

        $throttleKey = mb_strtolower($credentials['email']).'|'.$request->ip();

        // بررسی محدودیت نرخ
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "تلاش‌های ناموفق زیاد بوده است. لطفاً بعد از {$seconds} ثانیه دوباره امتحان کنید.",
            ]);
        }

        // فقط کاربران مدیر می‌توانند وارد پنل شوند
        $user = User::where('email', $credentials['email'])->where('is_admin', true)->first();

        if (! $user || ! Auth::validate(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'ایمیل یا رمز عبور اشتباه است.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // بازسازی شناسه سشن برای جلوگیری از Session Fixation
        $request->session()->regenerate();

        Auth::login($user, $request->boolean('remember'));

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
