<?php

if (! function_exists('pkr')) {
    /**
     * Format a number as Pakistani Rupees.
     *
     * @param  float|int|string $amount
     * @param  int              $decimals
     * @return string           e.g. "Rs. 1,250.00"
     */
    function pkr(float|int|string|null $amount = 0, int $decimals = 2): string
    {
        return 'Rs. ' . number_format((float) ($amount ?? 0), $decimals);
    }
}

if (! function_exists('format_qty')) {
    /**
     * Format product quantity without forced integer truncation or trailing zeros.
     * e.g. 5.000 -> "5", 0.500 -> "0.5", 1.250 -> "1.25", 0.125 -> "0.125"
     *
     * @param  float|int|string|null $qty
     * @param  int                   $maxDecimals
     * @return string
     */
    function format_qty(float|int|string|null $qty = 0, int $maxDecimals = 3): string
    {
        $val = (float) ($qty ?? 0);
        $formatted = number_format($val, $maxDecimals, '.', '');
        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }
}

