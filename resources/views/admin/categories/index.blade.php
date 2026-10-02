@extends('layouts.admin')

@section('title', 'مدیریت دسته‌بندی‌ها')
@section('page_title', 'مدیریت دسته‌بندی‌ها')

@section('content')
    <div class="ds-page-head">
        <span class="text-muted small">{{ fa_num($categories->total()) }} دسته‌بندی</span>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-ds">
            <i class="fa-solid fa-plus ms-1"></i> دسته جدید
        </a>
    </div>

    @if($categories->isNotEmpty())
        <div class="ds-card p-2 p-md-3">
            <div class="table-responsive">
                <table class="table ds-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام دسته</th>
                            <th>آیکون</th>
                            <th>تعداد محصولات</th>
                            <th>اسلاگ</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td class="text-muted">{{ fa_num($category->id) }}</td>
                                <td class="fw-bold">
                                    <i class="fa-solid {{ $category->icon ?: 'fa-tag' }} ms-2 text-muted"></i>
                                    {{ $category->name }}
                                </td>
                                <td><code class="ds-code">{{ $category->icon ?: '—' }}</code></td>
                                <td>
                                    <span class="badge text-bg-primary">{{ fa_num($category->products_count) }}</span>
                                </td>
                                <td><code class="ds-code">{{ $category->slug }}</code></td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('shop.products.index', ['category' => $category->slug]) }}"
                                           class="btn btn-ds-ghost btn-sm" title="مشاهده در فروشگاه">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           class="btn btn-ds-ghost btn-sm" title="ویرایش">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn btn-ds-danger btn-sm" title="حذف"
                                                data-delete-form="delete-cat-{{ $category->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                        <form id="delete-cat-{{ $category->id }}"
                                              action="{{ route('admin.categories.destroy', $category) }}"
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
            {{ $categories->onEachSide(1)->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="ds-empty">
                <i class="fa-solid fa-layer-group"></i>
                <p class="mb-2">هنوز دسته‌بندی‌ای ثبت نشده است.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-ds mt-2">
                    <i class="fa-solid fa-plus ms-1"></i> افزودن دسته‌بندی
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
                title: 'حذف دسته‌بندی',
                text: 'دسته‌ای محصول نداشته باشد حذف می‌شود. مطمئن هستید؟',
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
