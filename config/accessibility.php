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
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the caching layer for performance optimizations.
    |
    */
    'cache' => [
        'default' => env('ACCESSIBILITY_CACHE_DRIVER', 'array'),

        'stores' => [
            'array' => [
                'driver' => 'array',
                'limit' => env('ACCESSIBILITY_CACHE_SIZE', 1000),
            ],

            'file' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/data/accessibility'),
            ],

            'null' => [
                'driver' => 'null',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles and Permissions
    |--------------------------------------------------------------------------
    |
    | Define the roles and permissions for the reporting features.
    |
    */
    'roles' => [
        'admin' => [
            'view_reports',
            'manage_reports',
            'view_dashboard',
            'manage_teams',
        ],
        'editor' => [
            'view_reports',
            'manage_reports',
            'view_dashboard',
        ],
        'viewer' => [
            'view_reports',
            'view_dashboard',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit and Reporting (CLI)
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'paths' => [
            base_path('resources/views'),
            base_path('resources/css'),
            base_path('public/css'),
        ],
        'include_extensions' => ['css', 'blade.php'],
        'exclude' => ['vendor', 'storage', 'node_modules'],
        'strictness' => 'AA',
    ],

    'report' => [
        'formats' => ['md'],
        'output_path' => storage_path('app/a11y'),
    ],

    'progress' => [
        'enabled' => true,
        'min_items_for_bar' => 50,
    ],
];
