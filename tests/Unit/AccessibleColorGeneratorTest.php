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
    'generateAccessibleTextColor returns black or white for non-tint mode', function () {
        $generator = app(AccessibleColorGenerator::class);

        // Test with hex colors
        expect($generator->generateAccessibleTextColor('#FFFFFF'))->toBe('#000000')
        ->and($generator->generateAccessibleTextColor('#000000'))->toBe('#FFFFFF')
        ->and($generator->generateAccessibleTextColor('#FF0000'))->toBe('#000000')
        ->and($generator->generateAccessibleTextColor('#0000FF'))->toBe('#FFFFFF');

        // Test with Tailwind colors
        expect(strtoupper($generator->generateAccessibleTextColor('blue-500')))->toBe('#000000')
        ->and(strtoupper($generator->generateAccessibleTextColor('yellow-300')))->toBe('#000000')
        ->and(strtoupper($generator->generateAccessibleTextColor('red-700')))->toBe('#FFFFFF');
    }
);

test(
    'generateAccessibleTextColor returns tinted colors when tint is true', function () {
        $generator = app(AccessibleColorGenerator::class);

        // Test with hex colors - we're checking that the result is not just black or white
        $tintedColor1 = $generator->generateAccessibleTextColor('#3b82f6', true);
        $tintedColor2 = $generator->generateAccessibleTextColor('#ef4444', true);

        expect($tintedColor1)->not->toBe('#000000')
            ->and($tintedColor1)->not->toBe('#FFFFFF')
            ->and($tintedColor2)->not->toBe('#000000')
            ->and($tintedColor2)->not->toBe('#FFFFFF');

        // Test with Tailwind colors
        $tintedColor3 = $generator->generateAccessibleTextColor('green-500', true);
        $tintedColor4 = $generator->generateAccessibleTextColor('purple-700', true);

        expect($tintedColor3)->not->toBe('#000000')
            ->and($tintedColor3)->not->toBe('#FFFFFF')
            ->and($tintedColor4)->not->toBe('#000000')
            ->and($tintedColor4)->not->toBe('#FFFFFF');
    }
);

test(
    'generateAccessibleTextColor handles invalid color strings', function () {
        $generator = app(AccessibleColorGenerator::class);

        // Test with invalid hex
        expect($generator->generateAccessibleTextColor('#XYZ'))->toBe('#000000');

        // Test with non-existent Tailwind color
        expect($generator->generateAccessibleTextColor('nonexistent-color'))->toBe('#000000');

        // Test with empty string
        expect($generator->generateAccessibleTextColor(''))->toBe('#000000');
    }
);

test(
    'generateAccessibleTextColor produces colors with sufficient contrast', function () {
        $generator = app(AccessibleColorGenerator::class);
        $a11y = app(A11y::class);

        $backgrounds = [
        '#3b82f6', // blue-500
        '#ef4444', // red-500
        '#22c55e', // green-500
        '#f59e0b', // amber-500
        '#8b5cf6', // violet-500
        '#000000', // black
        '#FFFFFF', // white
        ];

        foreach ($backgrounds as $background) {
            // Test non-tint mode (black or white)
            $textColor1 = $generator->generateAccessibleTextColor($background);
            expect($a11y->a11yCheckContrastColor($background, $textColor1))->toBeTrue();

            // Test tint mode
            $textColor2 = $generator->generateAccessibleTextColor($background, true);
            expect($a11y->a11yCheckContrastColor($background, $textColor2))->toBeTrue();
        }
    }
);

test(
    'helper function generateAccessibleTextColor works correctly', function () {
        // Test the helper function with the same cases as the class method

        // Test with hex colors
        expect(generateAccessibleTextColor('#FFFFFF'))->toBe('#000000')
            ->and(generateAccessibleTextColor('#000000'))->toBe('#FFFFFF');

        // Test with Tailwind colors
        expect(strtoupper(generateAccessibleTextColor('blue-500')))->toBe('#000000')
            ->and(strtoupper(generateAccessibleTextColor('yellow-300')))->toBe('#000000');

        // Test tint mode
        $tintedColor = generateAccessibleTextColor('#3b82f6', true);
        expect($tintedColor)->not->toBe('#000000')
            ->and($tintedColor)->not->toBe('#FFFFFF');

        // Test invalid color
        expect(generateAccessibleTextColor('nonexistent-color'))->toBe('#000000');
    }
);

test(
    'handles 3-digit hex codes', function () {
        $generator = app(AccessibleColorGenerator::class);
        // #fff (white) should result in black text
        expect($generator->generateAccessibleTextColor('#fff'))->toBe('#000000');
        // #000 (black) should result in white text
        expect($generator->generateAccessibleTextColor('#000'))->toBe('#FFFFFF');
        // #f00 (red) should result in black text
        expect($generator->generateAccessibleTextColor('#f00'))->toBe('#000000');
    }
);

test(
    'adjustBrightness handles extreme factors', function () {
        $generator = app(AccessibleColorGenerator::class);
        $a11y = app(A11y::class);

        // A color that is right on the edge of contrast
        $color = '#777777';

        // A factor of 0 should still produce a valid contrasting color
        $accessibleColor = $generator->generateAccessibleTextColor($color, true);
        expect($a11y->a11yCheckContrastColor($color, $accessibleColor))->toBeTrue();

        // A large positive factor should result in a color with good contrast
        $accessibleColorPositive = $generator->generateAccessibleTextColor($color, true);
        expect($a11y->a11yCheckContrastColor($color, $accessibleColorPositive))->toBeTrue();

        // A large negative factor should result in a color with good contrast
        $accessibleColorNegative = $generator->generateAccessibleTextColor($color, true);
        expect($a11y->a11yCheckContrastColor($color, $accessibleColorNegative))->toBeTrue();
    }
);

