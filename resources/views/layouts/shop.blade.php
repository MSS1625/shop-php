<!DOCTYPE html>
<html lang="fa" dir="rtl" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'فروشگاه آنلاین') | {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', 'فروشگاه آنلاین تجهیزات شبکه — خرید امن، سریع و با بهترین قیمت')">

    {{-- فونت وزیرمتن --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    {{-- بوت‌استرپ راست‌چین --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    {{-- آیکون‌ها --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    {{-- تم دیجی‌شاپ --}}
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    @stack('styles')
</head>
<body>

@hasSection('fullwidth')
    @yield('fullwidth')
@endif

<nav class="navbar navbar-expand-lg sticky-top ds-navbar">
    <div class="container">
        <a class="ds-brand" href="{{ route('shop.home') }}">
            <span class="ds-brand-icon"><i class="fa-solid fa-bolt"></i></span>
            دیجی‌شاپ
        </a>

        <button class="navbar-toggler border-0 text-light" type="button" data-bs-toggle="collapse" data-bs-target="#dsNav">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="dsNav">
            <ul class="navbar-nav mx-auto gap-lg-2 my-3 my-lg-0">
                <li class="nav-item">
                    <a class="ds-nav-link {{ request()->routeIs('shop.home') ? 'active' : '' }}"
                       href="{{ route('shop.home') }}">
                        <i class="fa-solid fa-house"></i> خانه
                    </a>
                </li>
                <li class="nav-item">
                    <a class="ds-nav-link {{ request()->routeIs('shop.products.*') ? 'active' : '' }}"
                       href="{{ route('shop.products.index') }}">
                        <i class="fa-solid fa-store"></i> محصولات
                    </a>
                </li>
            </ul>

            <form class="ds-search d-none d-lg-block" action="{{ route('shop.products.index') }}" method="GET" role="search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="جستجوی محصول..." aria-label="جستجو">
            </form>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <a href="{{ route('cart.index') }}" class="ds-cart-btn" title="سبد خرید" aria-label="سبد خرید">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="ds-cart-count" {{ $cartCount ?? 0 > 0 ? '' : 'style="display:none"' }}>
                        {{ fa_num($cartCount ?? 0) }}
                    </span>
                </a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-ds-outline d-none d-sm-inline-flex">
                            <i class="fa-solid fa-gauge-high ms-1"></i> پنل مدیریت
                        </a>
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-ds-ghost dropdown-toggle d-flex align-items-center gap-2"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="ds-avatar"><i class="fa-solid fa-user"></i></span>
                            <span class="d-none d-md-inline text-truncate" style="max-width:110px">
                                {{ auth()->user()->name }}
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark ds-dropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('account.dashboard') }}">
                                    <i class="fa-solid fa-user ms-1"></i> حساب کاربری
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('account.orders') }}">
                                    <i class="fa-solid fa-box-open ms-1"></i> سفارش‌های من
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fa-solid fa-right-from-bracket ms-1"></i> خروج
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ds-ghost d-none d-sm-inline-flex">
                        <i class="fa-solid fa-user ms-1"></i> ورود
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-ds-outline d-none d-sm-inline-flex">
                        <i class="fa-solid fa-user-plus ms-1"></i> ثبت‌نام
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main>
    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-ds-success d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="container mt-3">
            <div class="alert alert-ds-error d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-xmark"></i>
                <div>{{ session('error') }}</div>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="container mt-3">
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>{{ session('warning') }}</div>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="ds-footer">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-6 text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                    <span class="ds-brand-icon" style="width:34px;height:34px;font-size:.9rem;border-radius:10px">
                        <i class="fa-solid fa-bolt"></i>
                    </span>
                    <strong class="text-light">{{ config('app.name') }}</strong>
                </div>
                <small>فروشگاه آنلاین تجهیزات شبکه — خرید امن، سریع و با بهترین قیمت</small>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <small>طراحی و توسعه با <span style="color:#f87171">❤</span> توسط محمد صادق صداقت</small>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/shop.js') }}"></script>
@stack('scripts')
</body>
</html>
