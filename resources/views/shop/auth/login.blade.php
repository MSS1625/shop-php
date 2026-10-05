@extends('layouts.shop')

@section('title', 'ورود به حساب کاربری')

@section('content')
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="ds-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="ds-stat-icon purple mx-auto mb-3" style="width:70px;height:70px;font-size:1.6rem;border-radius:20px">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <h1 class="fs-4 fw-bold mb-1">ورود به حساب کاربری</h1>
                        <p class="text-muted small mb-0">برای پیگیری سفارش‌ها وارد شوید</p>
                    </div>

                    <form action="{{ route('login.attempt') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">ایمیل</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   dir="ltr" required autofocus>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" dir="ltr" required>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label small" for="remember">مرا به خاطر بسپار</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-ds w-100">
                            <i class="fa-solid fa-right-to-bracket ms-1"></i> ورود
                        </button>
                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        حساب کاربری ندارید؟
                        <a href="{{ route('register') }}" class="text-info">ثبت‌نام کنید</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
