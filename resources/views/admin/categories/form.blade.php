@extends('layouts.admin')

@section('title', $category->exists ? 'ویرایش دسته' : 'دسته جدید')
@section('page_title', $category->exists ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="ds-card p-4">
                <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
                      method="POST">
                    @csrf
                    @if($category->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">نام دسته</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="icon" class="form-label">آیکون (کلاس Font Awesome)</label>
                        <input type="text" class="form-control @error('icon') is-invalid @enderror"
                               id="icon" name="icon" value="{{ old('icon', $category->icon) }}"
                               placeholder="fa-network-wired" dir="ltr">
                        <div class="form-text">
                            مثال: <code class="ds-code">fa-wifi</code> یا <code class="ds-code">fa-screwdriver-wrench</code>
                            — از <a href="https://fontawesome.com/search?ic=free" target="_blank" rel="noopener" class="text-info">آیکون‌های رایگان</a> استفاده کنید.
                        </div>
                        @error('icon')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-ds px-4">
                            <i class="fa-solid fa-floppy-disk ms-1"></i>
                            {{ $category->exists ? 'بروزرسانی' : 'ذخیره دسته' }}
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-ds-ghost">
                            <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
