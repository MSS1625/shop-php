@extends('layouts.admin')

@section('title', $coupon->exists ? 'ویرایش کد تخفیف' : 'کد تخفیف جدید')
@section('page_title', $coupon->exists ? 'ویرایش کد تخفیف' : 'افزودن کد تخفیف جدید')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="ds-card p-4">
                <form action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}"
                      method="POST">
                    @csrf
                    @if($coupon->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="code" class="form-label">کد تخفیف</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                       id="code" name="code" value="{{ old('code', $coupon->code) }}"
                                       dir="ltr" placeholder="SUMMER20" required>
                                <div class="form-text">حروف انگلیسی، عدد و خط تیره — کوچک/بزرک بودن اهمیتی ندارد.</div>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-5 mb-3">
                                    <label for="type" class="form-label">نوع تخفیف</label>
                                    <select class="form-select @error('type') is-invalid @enderror"
                                            id="type" name="type" required>
                                        <option value="percent" @selected(old('type', $coupon->type) === 'percent')>درصدی</option>
                                        <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>مبلغ ثابت</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-7 mb-3">
                                    <label for="value" class="form-label">مقدار <span class="text-muted small">(درصد یا تومان)</span></label>
                                    <input type="number" class="form-control @error('value') is-invalid @enderror"
                                           id="value" name="value" value="{{ old('value', $coupon->value) }}"
                                           min="1" required>
                                    @error('value')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="min_order_amount" class="form-label">حداقل مبلغ سبد <span class="text-muted small">(تومان)</span></label>
                                    <input type="number" class="form-control @error('min_order_amount') is-invalid @enderror"
                                           id="min_order_amount" name="min_order_amount"
                                           value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}"
                                           min="0" placeholder="0 = بدون محدودیت">
                                    @error('min_order_amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="usage_limit" class="form-label">سقف تعداد استفاده</label>
                                    <input type="number" class="form-control @error('usage_limit') is-invalid @enderror"
                                           id="usage_limit" name="usage_limit"
                                           value="{{ old('usage_limit', $coupon->usage_limit) }}"
                                           min="1" placeholder="خالی = نامحدود">
                                    @error('usage_limit')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="starts_at" class="form-label">تاریخ شروع</label>
                                <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror"
                                       id="starts_at" name="starts_at" dir="ltr"
                                       value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}">
                                <div class="form-text">خالی بگذارید تا از همین حالا معتبر باشد.</div>
                                @error('starts_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="expires_at" class="form-label">تاریخ پایان</label>
                                <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror"
                                       id="expires_at" name="expires_at" dir="ltr"
                                       value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}">
                                <div class="form-text">خالی بگذارید تا بدون انقضا باشد.</div>
                                @error('expires_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check form-switch mb-3 mt-4">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="is_active" name="is_active" value="1"
                                       @checked(old('is_active', $coupon->is_active ?? true))>
                                <label class="form-check-label" for="is_active">کد فعال باشد</label>
                            </div>

                            @if($coupon->exists && $coupon->used_count > 0)
                                <div class="alert alert-info small mb-0 py-2">
                                    <i class="fa-solid fa-circle-info ms-1"></i>
                                    این کد تاکنون {{ $coupon->usageLabel() }} استفاده شده است.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center mt-4">
                        <button type="submit" class="btn btn-ds px-4">
                            <i class="fa-solid fa-circle-check ms-1"></i>
                            {{ $coupon->exists ? 'بروزرسانی کد' : 'ثبت کد تخفیف' }}
                        </button>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-ds-ghost px-4">
                            بازگشت
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
