<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store contact details — used in public catalog
    |--------------------------------------------------------------------------
    */
    'name'      => env('STORE_NAME',      'HardwarePro'),
    'phone'     => env('STORE_PHONE',     '+923001234567'),
    'whatsapp'  => env('STORE_WHATSAPP',  '923001234567'),  // No + for wa.me links
    'address'   => env('STORE_ADDRESS',   ''),
    'tagline'   => env('STORE_TAGLINE',   'Your Trusted Hardware & Sanitary Store'),
];
