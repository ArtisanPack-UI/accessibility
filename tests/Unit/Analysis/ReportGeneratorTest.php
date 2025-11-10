<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Analysis;

use ArtisanPack\Accessibility\Core\Analysis\AccessibilityScorer;
use ArtisanPack\Accessibility\Core\Analysis\ColorBlindnessSimulator;
use ArtisanPack\Accessibility\Core\Analysis\PerceptualAnalyzer;
use ArtisanPack\Accessibility\Core\Analysis\ReportGenerator;
use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\Caching\CacheManager;
use ArtisanPack\Accessibility\Core\Theming\CssVariableParser;
use ArtisanPack\Accessibility\Core\WcagValidator;

it(
    'generates a comprehensive analysis report', function () {
        $wcagValidator = new WcagValidator();
        $parser = new CssVariableParser();
        $cacheManager = new CacheManager(['default' => 'array', 'stores' => ['array' => ['driver' => 'array']]]);
        $colorGenerator = new AccessibleColorGenerator($wcagValidator, $parser, $cacheManager, null);
        $scorer = new AccessibilityScorer($wcagValidator, $colorGenerator);
        $simulator = new ColorBlindnessSimulator();
        $analyzer = new PerceptualAnalyzer();
        $generator = new ReportGenerator($simulator, $analyzer, $scorer);

        $report = $generator->generate('#ff0000', '#ffffff');

        expect($report)->toBeArray();
        expect($report)->toHaveKeys(
            [
                'color_blindness_simulation',
                'perceptual_analysis',
                'accessibility_score',
                'recommendations',
            ]
        );

        expect($report['color_blindness_simulation'])->toBeArray();
        expect($report['color_blindness_simulation'])->toHaveKeys(['protanopia', 'deuteranopia', 'tritanopia']);

        expect($report['perceptual_analysis'])->toBeArray();
        expect($report['perceptual_analysis'])->toHaveKeys(['delta_e', 'complementary', 'analogous', 'triadic']);

        expect($report['accessibility_score'])->toBeInt();

        expect($report['recommendations'])->toBeArray();
    }
);
