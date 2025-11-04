<?php

use ArtisanPack\Accessibility\Core\Theming\ThemeValidator;
use ArtisanPack\Accessibility\Core\WcagValidator;

it(
    'validates a correct theme', function () {
        $wcagValidator = Mockery::mock(WcagValidator::class);
        $wcagValidator->shouldReceive('checkContrast')->andReturn(true);

        $validator = new ThemeValidator($wcagValidator);

        $theme = [
        'light' => ['--color-primary' => '#000000'],
        'dark' => ['--color-primary' => '#ffffff'],
        ];

        expect($validator->validate($theme))->toBeTrue();
    }
);

it(
    'rejects an invalid theme', function () {
        $wcagValidator = Mockery::mock(WcagValidator::class);
        $wcagValidator->shouldReceive('checkContrast')->andReturn(false);

        $validator = new ThemeValidator($wcagValidator);

        $theme = [
        'light' => ['--color-primary' => '#ffffff'],
        'dark' => ['--color-primary' => '#000000'],
        ];

        expect($validator->validate($theme))->toBeFalse();
    }
);
