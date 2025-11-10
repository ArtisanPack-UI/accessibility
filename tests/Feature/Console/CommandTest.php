<?php

use Illuminate\Support\Str;
use function Pest\Laravel\artisan;

uses(Tests\TestCase::class);

it('runs audit-colors and writes markdown by default', function () {
    $fixtures = __DIR__ . '/../../fixtures';

    // Ensure clean target
    $outDir = storage_path('app/a11y');
    if (is_dir($outDir)) {
        @array_map('unlink', glob($outDir . '/*'));
    } else {
        @mkdir($outDir, 0777, true);
    }

    $exit = $this->artisan('a11y:audit-colors', [
        '--path' => [$fixtures],
        '--strictness' => 'AA',
        '--output' => $outDir,
    ])->run();

    expect($exit)->toBeIn([0, 2]); // may return 2 if violations found

    $md = $outDir . '/a11y-audit.md';
    expect(file_exists($md))->toBeTrue();
    $contents = file_get_contents($md);
    expect($contents)->toContain('# Accessibility Color Audit');
});

it('writes reports in requested formats and generates a palette', function () {
    $fixtures = __DIR__ . '/../../fixtures';
    $outDir = storage_path('app/a11y');
    if (!is_dir($outDir)) @mkdir($outDir, 0777, true);

    $exit = $this->artisan('a11y:audit-colors', [
        '--path' => [$fixtures],
        '--format' => ['json', 'html'],
        '--output' => $outDir,
    ])->run();
    expect($exit)->toBeIn([0, 2]);

    expect(file_exists($outDir . '/a11y-audit.json'))->toBeTrue();
    expect(file_exists($outDir . '/a11y-audit.html'))->toBeTrue();

    $palettePath = $outDir . '/palette.json';
    $exit2 = $this->artisan('a11y:generate-palette', [
        '--primary' => '#3366FF',
        '--format' => 'json',
        '--output' => $palettePath,
    ])->run();

    expect($exit2)->toBe(0);
    expect(file_exists($palettePath))->toBeTrue();
    $json = json_decode(file_get_contents($palettePath), true);
    expect($json)->toBeArray();
    expect($json)->toHaveKey('palette');
});
