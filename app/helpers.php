<?php

if (! function_exists('pkr')) {
    /**
     * Format a number as Pakistani Rupees.
     *
     * @param  float|int|string $amount
     * @param  int              $decimals
     * @return string           e.g. "Rs. 1,250.00"
     */
    function pkr(float|int|string $amount, int $decimals = 2): string
    {
        return 'Rs. ' . number_format((float) $amount, $decimals);
    }
}
