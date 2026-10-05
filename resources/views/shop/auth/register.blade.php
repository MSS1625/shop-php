@extends('layouts.shop')

@section('title', 'ثبت‌نام')

@section('content')
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="ds-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="ds-stat-icon purple mx-auto mb-3" style="width:70px;height:70px;font-size:1.6rem;border-radius:20px">
                            <i class="fa-solid fa-user-plus"></i>
                        </span>
                        <h1 class="fs-4 fw-bold mb-1">ساخت حساب کاربری</h1>
                        <p class="text-muted small mb-0">با حساب کاربری می‌توانید سفارش‌هایتان را پیگیری کنید</p>
                    </div>

                    <form action="{{ route('register.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">نام و نام خانوادگی</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">ایمیل</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" dir="ltr" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">رمز عبور</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" dir="ltr" required>
                                <div class="form-text">حداقل ۸ کاراکتر — ترکیبی از حرف و عدد</div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">تکرار رمز عبور</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                       name="password_confirmation" dir="ltr" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-ds w-100 mt-2">
                            <i class="fa-solid fa-user-plus ms-1"></i> ثبت‌نام
                        </button>
                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        قبلا ثبت‌نام کرده‌اید؟
                        <a href="{{ route('login') }}" class="text-info">وارد شوید</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
