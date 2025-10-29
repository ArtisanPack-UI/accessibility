<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WCAG Contrast Thresholds
    |--------------------------------------------------------------------------
    |
    | The contrast ratio thresholds for WCAG compliance.
    |
    */
    'wcag_thresholds' => [
        'aa' => env('ACCESSIBILITY_WCAG_THRESHOLDS_AA', 4.5),
        'aaa' => env('ACCESSIBILITY_WCAG_THRESHOLDS_AAA', 7.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Large Text Thresholds
    |--------------------------------------------------------------------------
    |
    | The font size and weight that qualifies as "large text" according
    | to WCAG guidelines. Large text requires a lower contrast ratio.
    |
    */
    'large_text_thresholds' => [
        'font_size' => env('ACCESSIBILITY_LARGE_TEXT_FONT_SIZE', 18), // points
        'font_weight' => env('ACCESSIBILITY_LARGE_TEXT_FONT_WEIGHT', 'bold'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Size
    |--------------------------------------------------------------------------
    |
    | The maximum number of items to store in the contrast cache.
    |
    */
    'cache_size' => env('ACCESSIBILITY_CACHE_SIZE', 1000),
];
