<?php

namespace App\Mail;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuestionAnswered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Question $question,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'پاسخ پرسش شما دربارهٔ «'.$this->question->product->title.'» — دیجی‌شاپ',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.question-answered',
            with: [
                'question' => $this->question->load('product'),
            ],
        );
    }
}
