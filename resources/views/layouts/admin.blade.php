<!DOCTYPE html>
<html lang="fa" dir="rtl" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل مدیریت') | {{ config('app.name') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    @stack('styles')
</head>
<body class="ds-admin-body">

{{-- سایدبار --}}
<aside class="ds-sidebar">
    <a class="ds-brand" href="{{ route('admin.dashboard') }}">
        <span class="ds-brand-icon"><i class="fa-solid fa-bolt"></i></span>
        <div>
            دیجی‌شاپ
            <small class="d-block text-muted fw-normal" style="font-size:.7em">پنل مدیریت</small>
        </div>
    </a>

    <nav class="nav flex-column">
        <a class="ds-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="fa-solid fa-gauge-high"></i> داشبورد
        </a>

        <a class="ds-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
            <i class="fa-solid fa-box-open"></i> محصولات
        </a>

        <a class="ds-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
            <i class="fa-solid fa-layer-group"></i> دسته‌بندی‌ها
        </a>

        <a class="ds-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
            <i class="fa-solid fa-receipt"></i> سفارش‌ها
            @php($pendingCount = \App\Models\Order::where('status', \App\Models\Order::STATUS_PENDING)->count())
            @if($pendingCount > 0)
                <span class="badge rounded-pill text-bg-danger me-auto">{{ fa_num($pendingCount) }}</span>
            @endif
        </a>

        <hr class="border-secondary-subtle mx-2">

        <a class="ds-nav-link" href="{{ route('shop.home') }}">
            <i class="fa-solid fa-globe"></i> مشاهده فروشگاه
        </a>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="ds-nav-link w-100 border-0 bg-transparent text-start">
                <i class="fa-solid fa-right-from-bracket"></i> خروج
            </button>
        </form>
    </nav>
</aside>

{{-- محتوای اصلی --}}
<div class="ds-admin-main">
    <header class="ds-admin-topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-ds-ghost d-lg-none" data-sidebar-toggle aria-label="بازکردن منو">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="fw-bold">@yield('page_title', 'پنل مدیریت')</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small d-none d-sm-inline">
                <i class="fa-solid fa-user ms-1"></i> {{ auth()->user()->name }}
            </span>
            <div class="ds-brand-icon" style="width:38px;height:38px;border-radius:11px;font-size:.85rem">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
        </div>
    </header>

    <div class="ds-admin-content">
        @if (session('success'))
            <div class="alert alert-ds-success d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-ds-error d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-xmark"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/shop.js') }}"></script>
@stack('scripts')
</body>
</html>
