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
