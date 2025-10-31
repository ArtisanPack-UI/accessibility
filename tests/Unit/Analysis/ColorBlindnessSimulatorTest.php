<?php

namespace ArtisanPackUI\Accessibility\Tests\Unit\Analysis;

use ArtisanPackUI\Accessibility\Analysis\ColorBlindnessSimulator;

it('simulates protanopia', function () {
    $simulator = new ColorBlindnessSimulator();

    // Red
    expect($simulator->simulateProtanopia('#ff0000'))->toBe('#918e00');
    // Green
    expect($simulator->simulateProtanopia('#00ff00'))->toBe('#6e713e');
    // Blue
    expect($simulator->simulateProtanopia('#0000ff'))->toBe('#0000c1');
});

it('simulates deuteranopia', function () {
    $simulator = new ColorBlindnessSimulator();

    // Red
    expect($simulator->simulateDeuteranopia('#ff0000'))->toBe('#9fb300');
    // Green
    expect($simulator->simulateDeuteranopia('#00ff00'))->toBe('#604d4d');
    // Blue
    expect($simulator->simulateDeuteranopia('#0000ff'))->toBe('#0000b3');
});

it('simulates blurred vision', function () {
    $simulator = new ColorBlindnessSimulator();

    $blurred = $simulator->simulateBlurredVision('#ff0000', 1);
    expect($blurred)->toBe('#f20d0d');
});

