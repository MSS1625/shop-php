<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * فرم ورود مشتری
     */
    public function showLogin(): View
    {
        return view('shop.auth.login');
    }

    /**
     * ورود کاربر (مشتری یا مدیر)
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'آدرس ایمیل معتبر نیست.',
            'password.required' => 'رمز عبور الزامی است.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            // پیام عمداً مبهم است تا حساب‌های موجود لو نروند
            throw ValidationException::withMessages([
                'email' => 'ایمیل یا رمز عبور نادرست است.',
            ]);
        }

        $request->session()->regenerate();

        // مدیرها به پنل مدیریت، مشتری‌ها به حساب کاربری
        if (Auth::user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()
            ->intended(route('account.dashboard'))
            ->with('success', 'خوش آمدید '.Auth::user()->name.'!');
    }

    /**
     * خروج (برای مشتری و مدیر)
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('shop.home')
            ->with('success', 'با موفقیت خارج شدید.');
    }
}
