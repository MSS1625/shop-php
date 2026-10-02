@extends('layouts.admin')

@section('title', $product->exists ? 'ویرایش محصول' : 'محصول جدید')
@section('page_title', $product->exists ? 'ویرایش محصول' : 'افزودن محصول جدید')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="ds-card p-4">
                <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($product->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">عنوان محصول</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title', $product->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="price" class="form-label">قیمت (تومان)</label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                           id="price" name="price" value="{{ old('price', $product->price) }}"
                                           min="0" required>
                                    @error('price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="stock" class="form-label">موجودی انبار</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                           id="stock" name="stock" value="{{ old('stock', $product->stock) }}"
                                           min="0" required>
                                    @error('stock')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">دسته‌بندی</label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id" required>
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="description" class="form-label">توضیحات محصول</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                                <div class="form-text">توضیحات کامل به فروش بیشتر کمک می‌کند.</div>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="imageInput" class="form-label">تصویر محصول</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                       id="imageInput" name="image" accept="image/jpeg,image/png,image/webp"
                                       {{ $product->exists ? '' : 'required' }}>
                                <div class="form-text">فرمت‌های مجاز: JPG، PNG، WebP — حداکثر ۲ مگابایت</div>
                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-center mb-3" data-preview-box {{ $product->imageExists() ? '' : 'style="display:none"' }}>
                                <img id="imagePreview" src="{{ $product->imageExists() ? $product->imageUrl() : '#' }}"
                                     alt="پیش‌نمایش" class="img-fluid rounded-3 border border-secondary-subtle"
                                     style="max-height:170px;object-fit:contain">
                                @if($product->exists)
                                    <small class="text-muted d-block mt-1">تصویر فعلی</small>
                                @endif
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">نمایش در فروشگاه</label>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary-subtle my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-ds px-4">
                            <i class="fa-solid fa-floppy-disk ms-1"></i>
                            {{ $product->exists ? 'بروزرسانی محصول' : 'ذخیره محصول' }}
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-ds-ghost">
                            <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
