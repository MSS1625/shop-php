<?php

if (! function_exists('fa_num')) {
    /**
     * تبدیل ارقام لاتین به فارسی — ۱۲۳۴۵
     */
    function fa_num(string|int|float|null $number): string
    {
        return str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            (string) $number
        );
    }
}

if (! function_exists('en_num')) {
    /**
     * تبدیل ارقام فارسی/عربی به لاتین — برای ورودی عددی فرم‌ها
     * (قرینهٔ fa_num؛ همان نقشهٔ نرمال‌سازی کد تخفیف و پیگیری سفارش)
     */
    function en_num(string|int|float|null $value): string
    {
        return strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}

if (! function_exists('fa_price')) {
    /**
     * قیمت با جداکننده هزارگان + ارقام فارسی — ۲٬۰۰۰٬۰۰۰
     */
    function fa_price(int|string|null $toman): string
    {
        if ($toman === null || $toman === '') {
            return '۰';
        }

        return fa_num(number_format((int) $toman));
    }
}
