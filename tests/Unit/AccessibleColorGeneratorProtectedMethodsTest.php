<?php

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\A11y;
use Tests\TestCase;

uses(TestCase::class);

it(
    'can be instantiated', function () {
        $generator = app(AccessibleColorGenerator::class);
        expect($generator)->toBeInstanceOf(AccessibleColorGenerator::class);
    } 
);

test(
    'getHexFromColorString converts Tailwind colors to hex', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('getHexFromColorString');
        $method->setAccessible(true);

        // Test with Tailwind colors
        expect($method->invoke($generator, 'blue-500'))->toBe('#3b82f6')
                                                       ->and($method->invoke($generator, 'red-700'))->toBe('#b91c1c')
                                                       ->and($method->invoke($generator, 'green-300'))->toBe('#86efac')
                                                       ->and($method->invoke($generator, 'white'))->toBe('#ffffff')
                                                       ->and($method->invoke($generator, 'black'))->toBe('#000000');
    } 
);

test(
    'getHexFromColorString handles hex colors correctly', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('getHexFromColorString');
        $method->setAccessible(true);

        // Test with hex colors
        expect($method->invoke($generator, '#3b82f6'))->toBe('#3b82f6')
                                                      ->and($method->invoke($generator, '#fff'))->toBe('#fff')
                                                      ->and($method->invoke($generator, '#000000'))->toBe('#000000');

        // Test with hex colors with whitespace
        expect($method->invoke($generator, ' #3b82f6 '))->toBe('#3b82f6');

        // Test with uppercase hex
        expect($method->invoke($generator, '#FFFFFF'))->toBe('#ffffff');
    } 
);

test(
    'getHexFromColorString returns null for invalid colors', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('getHexFromColorString');
        $method->setAccessible(true);

        // Test with invalid hex
        expect($method->invoke($generator, '#XYZ'))->toBeNull()
                                                   ->and($method->invoke($generator, '#12345'))->toBeNull()
                                                   ->and($method->invoke($generator, 'not-a-color'))->toBeNull()
                                                   ->and($method->invoke($generator, ''))->toBeNull();
    } 
);

test(
    'findClosestAccessibleShade returns a color with sufficient contrast', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('findClosestAccessibleShade');
        $method->setAccessible(true);

        $a11y = app(A11y::class);

        $testColors = [
        '#3b82f6', // blue-500
        '#ef4444', // red-500
        '#22c55e', // green-500
        '#f59e0b', // amber-500
        '#8b5cf6', // violet-500
        ];

        foreach ( $testColors as $color ) {
            $result = $method->invoke($generator, $color);
            expect($a11y->a11yCheckContrastColor($color, $result))->toBeTrue();
        }
    } 
);

test(
    'adjustBrightness correctly lightens colors', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('adjustBrightness');
        $method->setAccessible(true);

        // Test lightening colors
        $lightened = $method->invoke($generator, '#000000', 0.5);
        expect($lightened)->toBe('#808080');

        $lightened = $method->invoke($generator, '#ff0000', 0.2);
        expect($lightened)->toBe('#ff3333');
    } 
);

test(
    'adjustBrightness correctly darkens colors', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('adjustBrightness');
        $method->setAccessible(true);

        // Test darkening colors
        $darkened = $method->invoke($generator, '#ffffff', -0.5);
        expect($darkened)->toBe('#808080');

        $darkened = $method->invoke($generator, '#00ff00', -0.2);
        expect($darkened)->toBe('#00cc00');
    } 
);

test(
    'adjustBrightness handles 3-digit hex colors', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('adjustBrightness');
        $method->setAccessible(true);

        // Test with 3-digit hex
        $adjusted = $method->invoke($generator, '#f00', 0.2);
        expect($adjusted)->toBe('#ff3333');

        $adjusted = $method->invoke($generator, '#fff', -0.3);
        expect($adjusted)->toBe('#b3b3b3');
    } 
);

test(
    'adjustBrightness clamps values between 0 and 255', function () {
        $generator  = app(AccessibleColorGenerator::class);
        $reflection = new ReflectionClass($generator);
        $method     = $reflection->getMethod('adjustBrightness');
        $method->setAccessible(true);

        // Test extreme lightening (should clamp at 255)
        $extreme = $method->invoke($generator, '#ffffff', 1.0);
        expect($extreme)->toBe('#ffffff');

        // Test extreme darkening (should clamp at 0)
        $extreme = $method->invoke($generator, '#000000', -1.0);
        expect($extreme)->toBe('#000000');
    } 
);
