<?php

uses(Tests\TestCase::class);

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\WcagValidator;
use ArtisanPackUI\Hooks\Facades\Filter;

beforeEach(function () {
    Filter::removeAll('ap.accessibility.contrastThreshold');
    Filter::removeAll('ap.accessibility.contrastColorMap');
    Filter::removeAll('ap.accessibility.textColorGenerated');
});

it('ap.accessibility.contrastThreshold filter overrides the default WCAG AA threshold', function () {
    $validator = new WcagValidator;

    // #767676 on white has ratio ~4.54:1 → passes default AA (4.5).
    expect($validator->checkContrast('#767676', '#FFFFFF', 'AA'))->toBeTrue();

    $received = [];
    addFilter('ap.accessibility.contrastThreshold', function (float $ratio, string $context) use (&$received) {
        $received[] = [$ratio, $context];

        // Force AAA-level (7.0) threshold globally, regardless of caller.
        return 7.0;
    });

    expect($validator->checkContrast('#767676', '#FFFFFF', 'AA'))->toBeFalse();
    expect($received)->not->toBeEmpty();
    expect($received[0][0])->toBe(4.5);
    expect($received[0][1])->toBe('aa');
});

it('ap.accessibility.contrastThreshold filter receives large-text context', function () {
    $validator = new WcagValidator;
    $captured = null;

    addFilter('ap.accessibility.contrastThreshold', function (float $ratio, string $context) use (&$captured) {
        $captured = $context;

        return $ratio;
    });

    $validator->checkContrast('#FFFFFF', '#000000', 'AA', true);

    expect($captured)->toBe('aa-large');
});

it('ap.accessibility.contrastColorMap filter can register custom palette entries', function () {
    $generator = new AccessibleColorGenerator;

    // Unknown color name returns null before filtering.
    expect($generator->getHexFromColorString('brand-primary'))->toBeNull();

    addFilter('ap.accessibility.contrastColorMap', function (array $map) {
        $map['brand-primary'] = '#123456';

        return $map;
    });

    // Fresh generator so the cached map is loaded through the filter.
    $freshGenerator = new AccessibleColorGenerator;
    expect($freshGenerator->getHexFromColorString('brand-primary'))->toBe('#123456');
});

it('ap.accessibility.textColorGenerated filter can override the final decision', function () {
    $generator = new AccessibleColorGenerator;

    expect($generator->generateAccessibleTextColor('#FFFFFF'))->toBe('#000000');

    $captured = [];
    addFilter('ap.accessibility.textColorGenerated', function (string $color, string $background, bool $tinted) use (&$captured) {
        $captured[] = [$color, $background, $tinted];

        return '#ff00ff';
    });

    // Fresh generator so the cached bw.* entry does not hide the newly-run pipeline.
    $freshGenerator = new AccessibleColorGenerator;
    expect($freshGenerator->generateAccessibleTextColor('#FFFFFF'))->toBe('#ff00ff');
    expect($captured)->toHaveCount(1);
    expect($captured[0][0])->toBe('#000000');
    expect($captured[0][1])->toBe('#ffffff');
    expect($captured[0][2])->toBeFalse();
});

it('ap.accessibility.textColorGenerated filter also runs for tinted output', function () {
    $captured = null;
    addFilter('ap.accessibility.textColorGenerated', function (string $color, string $background, bool $tinted) use (&$captured) {
        $captured = $tinted;

        return $color;
    });

    $generator = new AccessibleColorGenerator;
    $generator->generateAccessibleTextColor('#3b82f6', true);

    expect($captured)->toBeTrue();
});
