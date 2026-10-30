<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\QuestionAnswered;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class QuestionController extends Controller
{
    /**
     * لیست پرسش‌ها + شمارندهٔ تب‌ها — فیلتر وضعیت اختیاری از query string
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        if (! is_string($status) || ! in_array($status, Question::STATUSES, true)) {
            $status = null;
        }

        $questions = Question::with(['product:id,title,slug', 'user:id,name'])
            ->status($status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.questions.index', [
            'questions' => $questions,
            'status' => $status,
            'counts' => [
                'all' => Question::count(),
                Question::STATUS_PENDING => Question::where('status', Question::STATUS_PENDING)->count(),
                Question::STATUS_ANSWERED => Question::where('status', Question::STATUS_ANSWERED)->count(),
                Question::STATUS_REJECTED => Question::where('status', Question::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    /**
     * ثبت یا ویرایش پاسخ — پرسش «پاسخ داده شد» می‌شود،
     * در صفحهٔ محصول نمایش داده می‌شود و پاسخ به ایمیل پرسنده می‌رود
     */
    public function answer(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'answer.required' => 'متن پاسخ الزامی است.',
            'answer.min' => 'متن پاسخ باید حداقل :min کاراکتر باشد.',
            'answer.max' => 'متن پاسخ حداکثر می‌تواند :max کاراکتر باشد.',
        ]);

        $wasAnswered = $question->isAnswered();

        $question->update([
            'answer' => trim($validated['answer']),
            'status' => Question::STATUS_ANSWERED,
            'answered_at' => $question->answered_at ?? now(),
        ]);

        // پاسخ تازه → به پرسنده خبر بده؛ ویرایش پاسخ → بی‌صدا
        if (! $wasAnswered && $question->user?->email) {
            try {
                Mail::to($question->user->email)->send(new QuestionAnswered($question));
            } catch (Throwable $e) {
                // خطای ارسال هرگز پاسخ‌دادن را نمی‌شکند — فقط لاگ
                Log::warning("question answered email failed for question {$question->id}: {$e->getMessage()}");
            }
        }

        $message = $wasAnswered
            ? 'پاسخ به‌روزرسانی شد.'
            : 'پاسخ ثبت شد، پرسش در صفحهٔ محصول نمایش داده می‌شود و به پرسنده ایمیل زده شد.';

        return back()->with('success', $message);
    }

    /**
     * رد پرسش — از صفحهٔ محصول حذف می‌شود اما رکورد می‌ماند
     */
    public function reject(Question $question): RedirectResponse
    {
        $question->update(['status' => Question::STATUS_REJECTED]);

        return back()->with('success', 'پرسش رد شد و در صفحهٔ محصول نمایش داده نمی‌شود.');
    }

    /**
     * حذف کامل پرسش
     */
    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('success', 'پرسش حذف شد.');
    }
}
