<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Analysis;

use ArtisanPack\Accessibility\Core\Analysis\AccessibilityScorer;
use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\Caching\CacheManager;
use ArtisanPack\Accessibility\Core\Theming\CssVariableParser;
use ArtisanPack\Accessibility\Core\WcagValidator;

it(
    'gets accessibility recommendations', function () {
        $wcagValidator = new WcagValidator();
        $parser = new CssVariableParser();
        $cacheManager = new CacheManager(['default' => 'array', 'stores' => ['array' => ['driver' => 'array']]]);
        $colorGenerator = new AccessibleColorGenerator($wcagValidator, $parser, $cacheManager, null);
        $scorer = new AccessibilityScorer($wcagValidator, $colorGenerator);

        // Black on White
        $recommendations = $scorer->getRecommendations('#000000', '#ffffff');
        expect($recommendations)->toBe(['message' => 'All WCAG standards are met.']);

        // Red on White
        $recommendations = $scorer->getRecommendations('#ff0000', '#ffffff');
        expect($recommendations)->toHaveKeys(['AA_normal', 'AAA_normal', 'AAA_large']);

        // Grey on White
        $recommendations = $scorer->getRecommendations('#777777', '#ffffff');
        expect($recommendations)->toHaveKeys(['AA_normal', 'AAA_normal', 'AAA_large']);
    }
);
