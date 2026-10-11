@extends('layouts.admin')

@section('title', 'مدیریت کدهای تخفیف')
@section('page_title', 'مدیریت کدهای تخفیف')

@section('content')
    <div class="ds-page-head">
        <span class="text-muted small">
            {{ fa_num($coupons->total()) }} کد ثبت شده است
        </span>

        <a href="{{ route('admin.coupons.create') }}" class="btn btn-ds">
            <i class="fa-solid fa-plus ms-1"></i> کد تخفیف جدید
        </a>
    </div>

    @if($coupons->isNotEmpty())
        <div class="ds-card p-2 p-md-3">
            <div class="table-responsive">
                <table class="table ds-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کد</th>
                            <th>نوع</th>
                            <th>مقدار تخفیف</th>
                            <th>حداقل سبد</th>
                            <th>مصرف</th>
                            <th>بازه اعتبار</th>
                            <th>وضعیت</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                            <tr>
                                <td class="text-muted">{{ fa_num($coupon->id) }}</td>
                                <td><code dir="ltr" class="fs-6">{{ $coupon->code }}</code></td>
                                <td>
                                    <span class="badge {{ $coupon->type === \App\Models\Coupon::TYPE_PERCENT ? 'text-bg-info' : 'text-bg-primary' }}">
                                        {{ \App\Models\Coupon::TYPE_LABELS[$coupon->type] }}
                                    </span>
                                </td>
                                <td class="fw-bold text-success">{{ $coupon->discountLabel() }}</td>
                                <td>
                                    {{ $coupon->min_order_amount > 0 ? fa_price($coupon->min_order_amount).' تومان' : '—' }}
                                </td>
                                <td>{{ $coupon->usageLabel() }}</td>
                                <td class="small text-muted" dir="ltr">
                                    @if($coupon->starts_at || $coupon->expires_at)
                                        {{ $coupon->starts_at?->format('Y-m-d') ?? '…' }}
                                        تا
                                        {{ $coupon->expires_at?->format('Y-m-d') ?? '…' }}
                                    @else
                                        بدون محدودیت
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $coupon->statusColor() }}">
                                        {{ $coupon->statusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}"
                                           class="btn btn-ds-ghost btn-sm" title="ویرایش">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn btn-ds-danger btn-sm" title="حذف"
                                                data-delete-form="delete-coupon-{{ $coupon->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                        <form id="delete-coupon-{{ $coupon->id }}"
                                              action="{{ route('admin.coupons.destroy', $coupon) }}"
                                              method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $coupons->onEachSide(1)->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="ds-empty">
                <i class="fa-solid fa-ticket"></i>
                <p class="mb-2">هنوز کد تخفیفی ثبت نشده است.</p>
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-ds mt-2">
                    <i class="fa-solid fa-plus ms-1"></i> ساخت اولین کد تخفیف
                </a>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-delete-form]');
            if (!btn) return;

            e.preventDefault();
            Swal.fire({
                title: 'حذف کد تخفیف',
                text: 'سفارش‌های قبلی این کد را به‌صورت متن نگه می‌دارند. مطمئن هستید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f87171',
                cancelButtonColor: '#8b5cf6',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(btn.dataset.deleteForm).submit();
                }
            });
        });
    </script>
@endpush
