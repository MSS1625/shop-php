<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * فرم ثبت‌نام مشتری
     */
    public function create(): View
    {
        return view('shop.auth.register');
    }

    /**
     * ساخت حساب کاربری و ورود خودکار
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'نام و نام خانوادگی الزامی است.',
            'name.min' => 'نام باید حداقل :min کاراکتر باشد.',
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'آدرس ایمیل معتبر نیست.',
            'email.unique' => 'این ایمیل قبلا ثبت شده است؛ وارد شوید.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل :min کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('account.dashboard')
            ->with('success', 'حساب کاربری شما ساخته شد؛ خوش آمدید!');
    }
}
