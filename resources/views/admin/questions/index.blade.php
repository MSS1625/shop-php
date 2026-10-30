@extends('layouts.admin')

@section('title', 'مدیریت پرسش‌ها')
@section('page_title', 'مدیریت پرسش‌ها')

@section('content')
    {{-- فیلتر وضعیت (همان الگوی سفارش‌ها و نظرات) --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.questions.index') }}"
           class="btn btn-sm {{ $status ? 'btn-ds-ghost' : 'btn-ds' }} rounded-pill">
            همه ({{ fa_num($counts['all']) }})
        </a>
        @foreach(\App\Models\Question::STATUS_LABELS as $key => $label)
            <a href="{{ route('admin.questions.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
                {{ $label }} ({{ fa_num($counts[$key]) }})
            </a>
        @endforeach
    </div>

    @if($questions->isNotEmpty())
        <div class="ds-card p-2 p-md-3">
            <div class="table-responsive">
                <table class="table ds-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کاربر</th>
                            <th>محصول</th>
                            <th>پرسش</th>
                            <th>تاریخ</th>
                            <th>وضعیت</th>
                            <th style="min-width: 280px">پاسخ و عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($questions as $question)
                            <tr>
                                <td class="text-muted">{{ fa_num($question->id) }}</td>
                                <td class="fw-bold">{{ $question->user?->name ?? 'کاربر حذف‌شده' }}</td>
                                <td>
                                    @if($question->product)
                                        <a href="{{ route('shop.products.show', $question->product->slug) }}"
                                           class="text-info" target="_blank">
                                            {{ $question->product->title }}
                                        </a>
                                    @else
                                        <span class="text-muted">محصول حذف‌شده</span>
                                    @endif
                                </td>
                                <td class="small" style="max-width: 260px">{{ $question->excerpt() }}</td>
                                <td class="text-muted small" dir="ltr">{{ $question->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $question->statusColor() }}">{{ $question->statusLabel() }}</span>
                                </td>
                                <td>
                                    {{-- پاسخ: فرم درج برای در انتظارها، ویرایش برای پاسخ‌داده‌ها، هیچ برای ردشده‌ها --}}
                                    @if($question->status === \App\Models\Question::STATUS_REJECTED)
                                        <span class="text-muted small">پرسش رد شده است.</span>
                                    @else
                                        <form action="{{ route('admin.questions.answer', $question) }}" method="POST" class="mb-2">
                                            @csrf
                                            @method('PATCH')
                                            <textarea name="answer" rows="2"
                                                      class="ds-filter-input w-100 mb-1"
                                                      placeholder="متن پاسخ…">{{ old('answer', $question->answer) }}</textarea>
                                            @error('answer')
                                                <div class="text-danger small mb-1">{{ $message }}</div>
                                            @enderror
                                            <button type="submit" class="btn btn-ds btn-sm">
                                                <i class="fa-solid fa-reply ms-1"></i>
                                                {{ $question->isAnswered() ? 'به‌روزرسانی پاسخ' : 'ثبت پاسخ' }}
                                            </button>
                                        </form>
                                    @endif

                                    <div class="d-flex gap-1">
                                        @unless($question->status === \App\Models\Question::STATUS_REJECTED)
                                            <form action="{{ route('admin.questions.reject', $question) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-ds-ghost btn-sm" title="رد">
                                                    <i class="fa-solid fa-xmark text-danger"></i>
                                                </button>
                                            </form>
                                        @endunless

                                        <button type="button" class="btn btn-ds-danger btn-sm" title="حذف"
                                                data-delete-form="delete-question-{{ $question->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                        <form id="delete-question-{{ $question->id }}"
                                              action="{{ route('admin.questions.destroy', $question) }}"
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
            {{ $questions->onEachSide(1)->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="ds-empty">
                <i class="fa-solid fa-circle-question"></i>
                <p class="mb-0">پرسشی با این فیلتر پیدا نشد.</p>
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
                title: 'حذف پرسش',
                text: 'این پرسش به‌طور کامل حذف می‌شود. مطمئن هستید؟',
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
