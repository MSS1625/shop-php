<!DOCTYPE html>
<html lang="fa" dir="rtl" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ورود مدیر | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
</head>
<body>
    <div class="ds-auth-wrap">
        <div class="ds-auth-card">
            <div class="text-center mb-4">
                <div class="ds-brand-icon mx-auto mb-3" style="width:64px;height:64px;font-size:1.5rem;border-radius:18px">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h1 class="h4 fw-bold">ورود مدیر فروشگاه</h1>
                <p class="text-muted small mb-0">برای ورود به پنل مدیریت اطلاعات خود را وارد کنید</p>
            </div>

            @if (session('status'))
                <div class="alert alert-ds-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">ایمیل</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="admin@example.com" dir="ltr" required autofocus>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">رمز عبور</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" placeholder="••••••••" dir="ltr" required>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-muted" for="remember">مرا به خاطر بسپار</label>
                </div>

                <button type="submit" class="btn btn-ds w-100 py-2">
                    <i class="fa-solid fa-right-to-bracket ms-1"></i> ورود به پنل
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('shop.home') }}" class="text-muted small text-decoration-none">
                    <i class="fa-solid fa-arrow-right"></i> بازگشت به فروشگاه
                </a>
            </div>
        </div>
    </div>
</body>
</html>
