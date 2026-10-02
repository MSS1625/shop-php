@extends('layouts.admin')

@section('title', 'مدیریت محصولات')
@section('page_title', 'مدیریت محصولات')

@section('content')
    <div class="ds-page-head">
        <form action="{{ route('admin.products.index') }}" method="GET" class="ds-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="{{ $term }}" placeholder="جستجوی محصول...">
        </form>

        <a href="{{ route('admin.products.create') }}" class="btn btn-ds">
            <i class="fa-solid fa-plus ms-1"></i> محصول جدید
        </a>
    </div>

    @if($products->isNotEmpty())
        <div class="ds-card p-2 p-md-3">
            <div class="table-responsive">
                <table class="table ds-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تصویر</th>
                            <th>عنوان</th>
                            <th>دسته</th>
                            <th>قیمت (تومان)</th>
                            <th>موجودی</th>
                            <th>وضعیت</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td class="text-muted">{{ fa_num($product->id) }}</td>
                                <td><img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" class="ds-thumb"></td>
                                <td>
                                    <div class="fw-bold">{{ $product->title }}</div>
                                    <small class="text-muted" dir="ltr">{{ $product->slug }}</small>
                                </td>
                                <td>
                                    @if($product->category)
                                        <span class="ds-product-cat">{{ $product->category->name }}</span>
                                    @endif
                                </td>
                                <td class="text-success">{{ fa_price($product->price) }}</td>
                                <td>
                                    <span class="badge {{ $product->stock === 0 ? 'text-bg-danger' : ($product->stock <= 3 ? 'text-bg-warning' : 'text-bg-success') }}">
                                        {{ fa_num($product->stock) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $product->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.products.edit', $product) }}"
                                           class="btn btn-ds-ghost btn-sm" title="ویرایش">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="{{ route('shop.products.show', $product) }}"
                                           class="btn btn-ds-ghost btn-sm" title="مشاهده در فروشگاه">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-ds-danger btn-sm" title="حذف"
                                                data-delete-form="delete-form-{{ $product->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                        <form id="delete-form-{{ $product->id }}"
                                              action="{{ route('admin.products.destroy', $product) }}"
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
            {{ $products->onEachSide(1)->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="ds-empty">
                <i class="fa-solid fa-box-open"></i>
                <p class="mb-2">{{ $term ? 'محصولی مطابق جستجو پیدا نشد.' : 'هنوز محصولی ثبت نشده است.' }}</p>
                <a href="{{ route('admin.products.create') }}" class="btn btn-ds mt-2">
                    <i class="fa-solid fa-plus ms-1"></i> افزودن اولین محصول
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
                title: 'حذف محصول',
                text: 'این عملیات قابل بازگشت نیست! مطمئن هستید؟',
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
