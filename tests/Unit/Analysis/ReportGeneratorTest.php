<?php

namespace ArtisanPackUI\Accessibility\Tests\Unit\Analysis;

use ArtisanPackUI\Accessibility\Analysis\ReportGenerator;

it('generates a comprehensive analysis report', function () {
    $generator = new ReportGenerator();

    $report = $generator->generate('#ff0000', '#ffffff');

    expect($report)->toBeArray();
    expect($report)->toHaveKeys([
        'color_blindness_simulation',
        'perceptual_analysis',
        'accessibility_score',
        'recommendations',
    ]);

    expect($report['color_blindness_simulation'])->toBeArray();
    expect($report['color_blindness_simulation'])->toHaveKeys(['protanopia', 'deuteranopia', 'tritanopia']);

    expect($report['perceptual_analysis'])->toBeArray();
    expect($report['perceptual_analysis'])->toHaveKeys(['delta_e', 'complementary', 'analogous', 'triadic']);

    expect($report['accessibility_score'])->toBeInt();

    expect($report['recommendations'])->toBeArray();
});
