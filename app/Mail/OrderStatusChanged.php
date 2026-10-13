<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تغییر وضعیت سفارش '.$this->order->order_number.' به «'.$this->order->statusLabel().'» — دیجی‌شاپ',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.status-changed',
            with: [
                'order' => $this->order->load('items'),
                'oldStatusLabel' => Order::STATUS_LABELS[$this->oldStatus] ?? $this->oldStatus,
            ],
        );
    }
}
