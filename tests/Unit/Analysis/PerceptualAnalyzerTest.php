<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Analysis;

use ArtisanPack\Accessibility\Core\Analysis\PerceptualAnalyzer;

it('gets the complementary color', function () {
    $analyzer = new PerceptualAnalyzer();

    $complementary = $analyzer->getComplementaryColor('#ff0000');
    expect($complementary)->toBe('#00ffff');
});

it('gets the analogous colors', function () {
    $analyzer = new PerceptualAnalyzer();

    $analogous = $analyzer->getAnalogousColors('#ff0000');
    expect($analogous)->toBe(['#ff8000', '#ff0080']);
});

it('gets the triadic colors', function () {
    $analyzer = new PerceptualAnalyzer();

    $triadic = $analyzer->getTriadicColors('#ff0000');
    expect($triadic)->toBe(['#00ff00', '#0000ff']);
});

