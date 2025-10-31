<?php

namespace ArtisanPackUI\Accessibility\Tests\Unit\Analysis;

use ArtisanPackUI\Accessibility\Analysis\AccessibilityScorer;

it('gets accessibility recommendations', function () {
    $scorer = new AccessibilityScorer();

    // Black on White
    $recommendations = $scorer->getRecommendations('#000000', '#ffffff');
    expect($recommendations)->toBe(['message' => 'All WCAG standards are met.']);

    // Red on White
    $recommendations = $scorer->getRecommendations('#ff0000', '#ffffff');
    expect($recommendations)->toHaveKeys(['AA_normal', 'AAA_normal', 'AAA_large']);

    // Grey on White
    $recommendations = $scorer->getRecommendations('#777777', '#ffffff');
    expect($recommendations)->toHaveKeys(['AA_normal', 'AAA_normal', 'AAA_large']);
});
