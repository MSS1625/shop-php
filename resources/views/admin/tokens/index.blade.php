@extends('layouts.admin')

@section('title', 'توکن‌های API')
@section('page_title', 'توکن‌های API')

@section('content')
    {{-- صدور توکن جدید — مقدار کامل فقط یک‌بار در مودال نمایش داده می‌شود --}}
    <div class="ds-card p-3 mb-4">
        <div class="ds-page-head pb-2 mb-3 border-bottom border-secondary-subtle">
            <h2 class="ds-page-title">صدور توکن جدید</h2>
            <span class="text-muted small">مقدار کامل توکن فقط یک‌بار، بعد از صدور، در مودال نمایش داده می‌شود</span>
        </div>

        <form action="{{ route('admin.tokens.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-12 col-md-4">
                <label for="token-user" class="ds-filter-label">کاربر</label>
                <select id="token-user" name="user_id" class="ds-filter-input w-100" required>
                    <option value="">— انتخاب کاربر —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>
                            {{ $u->name }} — {{ $u->email }}@if($u->is_admin) (مدیر)@endif
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6 col-md-3">
                <label for="token-name" class="ds-filter-label">نام توکن (دستگاه)</label>
                <input id="token-name" name="name" value="{{ old('name') }}" class="ds-filter-input w-100"
                       placeholder="مثلاً iPhone 15" maxlength="100" required>
                @error('name')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6 col-md-2">
                <label for="token-expiry" class="ds-filter-label">مدت اعتبار</label>
                <select id="token-expiry" name="expires" class="ds-filter-input w-100">
                    <option value="never" @selected(old('expires') === 'never')>بدون انقضا</option>
                    <option value="7" @selected(old('expires') === '7')>۷ روز</option>
                    <option value="30" @selected(old('expires') === '30')>۳۰ روز</option>
                    <option value="90" @selected(old('expires') === '90')>۹۰ روز</option>
                </select>
                @error('expires')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-3 d-grid">
                <button type="submit" class="btn btn-ds">
                    <i class="fa-solid fa-key ms-1"></i> صدور توکن
                </button>
            </div>
        </form>
    </div>

    {{-- فیلتر تب‌ها (همان الگوی سفارش‌ها، نظرات و پرسش‌ها) --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.tokens.index') }}"
           class="btn btn-sm {{ $filter === 'all' ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
            همه ({{ fa_num($counts['all']) }})
        </a>
        <a href="{{ route('admin.tokens.index', ['filter' => 'unused']) }}"
           class="btn btn-sm {{ $filter === 'unused' ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
            بدون استفاده ({{ fa_num($counts['unused']) }})
        </a>
        <a href="{{ route('admin.tokens.index', ['filter' => 'expired']) }}"
           class="btn btn-sm {{ $filter === 'expired' ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
            منقضی‌شده ({{ fa_num($counts['expired']) }})
        </a>
    </div>

    @if($tokens->isNotEmpty())
        <div class="ds-card p-2 p-md-3">
            <div class="table-responsive">
                <table class="table ds-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کاربر</th>
                            <th>نام توکن</th>
                            <th>انقضا</th>
                            <th>آخرین استفاده</th>
                            <th>صدور</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tokens as $token)
                            @php($owner = $token->tokenable)
                            @php($isExpired = $token->expires_at !== null && $token->expires_at->isPast())
                            <tr>
                                <td class="text-muted">{{ fa_num($token->id) }}</td>
                                <td>
                                    @if($owner)
                                        <div class="fw-bold">
                                            {{ $owner->name }}
                                            @if($owner->is_admin)
                                                <span class="badge text-bg-info">مدیر</span>
                                            @endif
                                        </div>
                                        <small class="text-muted" dir="ltr">{{ $owner->email }}</small>
                                    @else
                                        <span class="text-muted">کاربر حذف‌شده</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $token->name }}</td>
                                <td>
                                    @if($token->expires_at === null)
                                        <span class="text-muted small">بدون انقضا</span>
                                    @elseif($isExpired)
                                        <span class="badge text-bg-danger">منقضی‌شده</span>
                                        <div class="text-muted small mt-1" dir="ltr">{{ $token->expires_at->format('Y-m-d') }}</div>
                                    @else
                                        <span class="text-muted small" dir="ltr">{{ $token->expires_at->format('Y-m-d') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($token->last_used_at === null)
                                        <span class="badge text-bg-warning">هرگز</span>
                                    @else
                                        <span class="text-muted small" dir="ltr">{{ $token->last_used_at->format('Y-m-d H:i') }}</span>
                                    @endif
                                </td>
                                <td class="text-muted small" dir="ltr">{{ $token->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <button type="button" class="btn btn-ds-danger btn-sm" title="ابطال توکن"
                                            data-revoke-form="revoke-token-{{ $token->id }}">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>

                                    <form id="revoke-token-{{ $token->id }}"
                                          action="{{ route('admin.tokens.destroy', $token) }}"
                                          method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $tokens->onEachSide(1)->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="ds-empty">
                <i class="fa-solid fa-key"></i>
                <p class="mb-0">توکنی با این فیلتر پیدا نشد.</p>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    {{-- مودال یک‌بارهٔ نمایش توکن تازه — مقدار توکن فقط در همین لحظه دیده می‌شود --}}
    @if(session('new_token'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const TOKEN = @json(session('new_token'));
                Swal.fire({
                    title: 'توکن «{{ session('new_token_name') }}» صادر شد',
                    html: '<p class="ds-token-note">مقدار کامل توکن را همین حالا در جای امن ذخیره کنید — دوباره نمایش داده نمی‌شود:</p>'
                        + '<div class="ds-token-code">' + TOKEN + '</div>',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'کپی و بستن',
                    confirmButtonColor: '#34d399',
                    cancelButtonText: 'بستن',
                    cancelButtonColor: '#8b5cf6',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed && navigator.clipboard) {
                        navigator.clipboard.writeText(TOKEN).then(function () {
                            Swal.fire('کپی شد!', 'توکن در کلیپ‌بورد است.', 'success');
                        });
                    }
                });
            });
        </script>
    @endif

    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-revoke-form]');
            if (!btn) return;

            e.preventDefault();
            Swal.fire({
                title: 'ابطال توکن',
                text: 'این توکن فوراً باطل می‌شود و دستگاه مربوطه باید دوباره وارد شود. مطمئن هستید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f87171',
                cancelButtonColor: '#8b5cf6',
                confirmButtonText: 'بله، ابطال کن',
                cancelButtonText: 'انصراف',
            }).then(function (result) {
                if (result.isConfirmed) {
                    document.getElementById(btn.dataset.revokeForm).submit();
                }
            });
        });
    </script>
@endpush
