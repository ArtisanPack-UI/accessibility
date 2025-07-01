<?php

use ArtisanPackUI\Accessibility\AccessibleColorGenerator;
use ArtisanPackUI\Accessibility\A11y;

// Simple test for AccessibleColorGenerator class
test('AccessibleColorGenerator can be instantiated', function () {
    $generator = new AccessibleColorGenerator();
    expect($generator)->toBeInstanceOf(AccessibleColorGenerator::class);
});

// Test public methods
test('generateAccessibleTextColor returns expected colors', function () {
    $generator = new AccessibleColorGenerator();

    // Test with hex colors (non-tint mode)
    expect($generator->generateAccessibleTextColor('#FFFFFF'))->toBe('#000000');
    expect($generator->generateAccessibleTextColor('#000000'))->toBe('#FFFFFF');

    // Test with Tailwind colors (non-tint mode)
    expect($generator->generateAccessibleTextColor('blue-500'))->toBe('#000000');
    expect($generator->generateAccessibleTextColor('yellow-300'))->toBe('#000000');

    // Test with tint mode
    $tintedColor = $generator->generateAccessibleTextColor('#3b82f6', true);
    expect($tintedColor)->not->toBe('#000000');
    expect($tintedColor)->not->toBe('#FFFFFF');

    // Test invalid colors
    expect($generator->generateAccessibleTextColor('#XYZ'))->toBe('#000000');
    expect($generator->generateAccessibleTextColor('nonexistent-color'))->toBe('#000000');
});

// Test helper function
test('helper function generateAccessibleTextColor works', function () {
    if (!function_exists('generateAccessibleTextColor')) {
        $this->markTestSkipped('Helper function not available in test environment');
    }

    // Basic test with hex color
    expect(generateAccessibleTextColor('#FFFFFF'))->toBe('#000000');
});

// Test that generated colors have sufficient contrast
test('generated colors have sufficient contrast', function () {
    $generator = new AccessibleColorGenerator();
    $a11y = new A11y();

    $backgrounds = [
        '#3b82f6', // blue-500
        '#ef4444', // red-500
        '#22c55e', // green-500
        '#000000', // black
        '#FFFFFF', // white
    ];

    foreach ($backgrounds as $background) {
        // Test non-tint mode
        $textColor = $generator->generateAccessibleTextColor($background);
        expect($a11y->a11yCheckContrastColor($background, $textColor))->toBeTrue();

        // Test tint mode
        $tintedColor = $generator->generateAccessibleTextColor($background, true);
        expect($a11y->a11yCheckContrastColor($background, $tintedColor))->toBeTrue();
    }
});
