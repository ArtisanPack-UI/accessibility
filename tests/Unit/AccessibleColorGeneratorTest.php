<?php

use ArtisanPackUI\Accessibility\AccessibleColorGenerator;
use ArtisanPackUI\Accessibility\A11y;
it('can be instantiated', function () {
    $generator = new AccessibleColorGenerator();
    expect($generator)->toBeInstanceOf(AccessibleColorGenerator::class);
});

test('generateAccessibleTextColor returns black or white for non-tint mode', function () {
    $generator = new AccessibleColorGenerator();

    // Test with hex colors
    expect($generator->generateAccessibleTextColor('#FFFFFF'))->toBe('#000000')
        ->and($generator->generateAccessibleTextColor('#000000'))->toBe('#FFFFFF')
        ->and($generator->generateAccessibleTextColor('#FF0000'))->toBe('#000000')
        ->and($generator->generateAccessibleTextColor('#0000FF'))->toBe('#FFFFFF');

    // Test with Tailwind colors
    expect(strtoupper($generator->generateAccessibleTextColor('blue-500')))->toBe('#000000')
        ->and(strtoupper($generator->generateAccessibleTextColor('yellow-300')))->toBe('#000000')
        ->and(strtoupper($generator->generateAccessibleTextColor('red-700')))->toBe('#FFFFFF');
});

test('generateAccessibleTextColor returns tinted colors when tint is true', function () {
    $generator = new AccessibleColorGenerator();

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
});

test('generateAccessibleTextColor handles invalid color strings', function () {
    $generator = new AccessibleColorGenerator();

    // Test with invalid hex
    expect($generator->generateAccessibleTextColor('#XYZ'))->toBe('#000000');

    // Test with non-existent Tailwind color
    expect($generator->generateAccessibleTextColor('nonexistent-color'))->toBe('#000000');

    // Test with empty string
    expect($generator->generateAccessibleTextColor(''))->toBe('#000000');
});

test('generateAccessibleTextColor produces colors with sufficient contrast', function () {
    $generator = new AccessibleColorGenerator();
    $a11y = new ArtisanPackUI\Accessibility\A11y();

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
});

test('helper function generateAccessibleTextColor works correctly', function () {
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
});
