@extends('layouts.admin')

@section('title', 'مدیریت نظرات')
@section('page_title', 'مدیریت نظرات')

@section('content')
    {{-- فیلتر وضعیت (همان الگوی سفارش‌ها) --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.reviews.index') }}"
           class="btn btn-sm {{ $status ? 'btn-ds-ghost' : 'btn-ds' }} rounded-pill">
            همه ({{ fa_num($counts['all']) }})
        </a>
        @foreach(\App\Models\Review::STATUS_LABELS as $key => $label)
            <a href="{{ route('admin.reviews.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
                {{ $label }} ({{ fa_num($counts[$key]) }})
            </a>
        @endforeach
    </div>

    @if($reviews->isNotEmpty())
        <div class="ds-card p-2 p-md-3">
            <div class="table-responsive">
                <table class="table ds-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کاربر</th>
                            <th>محصول</th>
                            <th>امتیاز</th>
                            <th>دیدگاه</th>
                            <th>تاریخ</th>
                            <th>وضعیت</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            <tr>
                                <td class="text-muted">{{ fa_num($review->id) }}</td>
                                <td class="fw-bold">{{ $review->user?->name ?? 'کاربر حذف‌شده' }}</td>
                                <td>
                                    @if($review->product)
                                        <a href="{{ route('shop.products.show', $review->product->slug) }}"
                                           class="text-info" target="_blank">
                                            {{ $review->product->title }}
                                        </a>
                                    @else
                                        <span class="text-muted">محصول حذف‌شده</span>
                                    @endif
                                </td>
                                <td>@include('shop.products.partials.stars', ['rating' => $review->rating])</td>
                                <td class="small" style="max-width: 320px">{{ $review->excerpt() }}</td>
                                <td class="text-muted small" dir="ltr">{{ $review->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $review->statusColor() }}">{{ $review->statusLabel() }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        @unless($review->status === \App\Models\Review::STATUS_APPROVED)
                                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-ds-ghost btn-sm" title="تأیید">
                                                    <i class="fa-solid fa-check text-success"></i>
                                                </button>
                                            </form>
                                        @endunless

                                        @unless($review->status === \App\Models\Review::STATUS_REJECTED)
                                            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-ds-ghost btn-sm" title="رد">
                                                    <i class="fa-solid fa-xmark text-danger"></i>
                                                </button>
                                            </form>
                                        @endunless

                                        <button type="button" class="btn btn-ds-danger btn-sm" title="حذف"
                                                data-delete-form="delete-review-{{ $review->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                        <form id="delete-review-{{ $review->id }}"
                                              action="{{ route('admin.reviews.destroy', $review) }}"
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
            {{ $reviews->onEachSide(1)->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="ds-empty">
                <i class="fa-solid fa-star-half-stroke"></i>
                <p class="mb-0">نظری با این فیلتر پیدا نشد.</p>
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
                title: 'حذف نظر',
                text: 'این نظر به‌طور کامل حذف می‌شود. مطمئن هستید؟',
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
