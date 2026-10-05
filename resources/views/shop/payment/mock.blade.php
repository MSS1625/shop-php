<!DOCTYPE html>
<html lang="fa" dir="rtl" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>درگاه آزمایشی پرداخت | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: #0f0f17;
            color: #e5e7eb;
            min-height: 100vh;
        }
        .gateway-card {
            max-width: 470px;
            margin: 3rem auto;
            background: #171722;
            border: 1px solid #2c2c3e;
            border-radius: 20px;
            overflow: hidden;
        }
        .gateway-header {
            background: linear-gradient(135deg, #f5c542, #e8891c);
            color: #1c1917;
            padding: 1.1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .gateway-body { padding: 1.5rem; }
        .amount-box {
            background: rgba(139, 92, 246, .12);
            border: 1px solid rgba(139, 92, 246, .35);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
        }
        .btn-pay {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            border: 0;
            border-radius: 12px;
            padding: .8rem;
            font-weight: 700;
        }
        .btn-pay:hover { color: #fff; filter: brightness(1.08); }
        .btn-cancel {
            background: transparent;
            border: 1px solid #dc3545;
            color: #f87171;
            border-radius: 12px;
            padding: .8rem;
            font-weight: 700;
        }
        .notice {
            background: rgba(245, 197, 66, .1);
            border: 1px dashed rgba(245, 197, 66, .5);
            color: #f5c542;
            border-radius: 12px;
            padding: .7rem 1rem;
            font-size: .8rem;
        }
    </style>
</head>
<body>
    <div class="gateway-card">
        <div class="gateway-header">
            <i class="fa-solid fa-vault fa-lg"></i>
            <div>
                <strong class="d-block">{{ $gatewayName }} — پرداخت آزمایشی</strong>
                <small>شبیه‌ساز محلی درگاه زرین‌پال</small>
            </div>
        </div>

        <div class="gateway-body">
            <div class="amount-box mb-3">
                <div class="text-muted small mb-1">مبلغ قابل پرداخت (سفارش <span dir="ltr">{{ $order->order_number }}</span>)</div>
                <div class="fs-3 fw-bold text-success">
                    {{ fa_price($order->total) }} <span class="fs-6">تومان</span>
                </div>
                <div class="text-muted small mt-2">
                    {{ fa_num($order->items->count()) }} قلم کالا
                </div>
            </div>

            <div class="notice mb-4">
                <i class="fa-solid fa-circle-info ms-1"></i>
                این صفحه فقط برای توسعه و تست است و هیچ پرداخت واقعی انجام نمی‌شود.
                برای پرداخت واقعی، <span dir="ltr">ZARINPAL_MODE=production</span> و
                <span dir="ltr">ZARINPAL_MERCHANT_ID</span> را در فایل
                <span dir="ltr">.env</span> تنظیم کنید.
            </div>

            <form action="{{ route('payment.mock.result', $order->order_number) }}" method="POST" class="d-grid gap-2">
                @csrf
                <input type="hidden" name="authority" value="{{ $order->authority }}">

                <button type="submit" name="result" value="success" class="btn btn-pay w-100">
                    <i class="fa-solid fa-circle-check ms-1"></i> پرداخت موفق (شبیه‌سازی)
                </button>
                <button type="submit" name="result" value="cancel" class="btn btn-cancel w-100">
                    <i class="fa-solid fa-circle-xmark ms-1"></i> انصراف از پرداخت
                </button>
            </form>
        </div>
    </div>
</body>
</html>
