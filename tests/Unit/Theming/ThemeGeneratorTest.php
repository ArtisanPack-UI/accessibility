<?php

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\Theming\CssVariableParser;
use ArtisanPack\Accessibility\Core\Theming\ThemeGenerator;
use ArtisanPack\Accessibility\Core\Theming\ThemeValidator;

it(
    'generates light and dark themes', function () {
        $colorGenerator = Mockery::mock(AccessibleColorGenerator::class);
        $parser = new CssVariableParser();

        $colorGenerator->shouldReceive('getHexFromColorString')->with('#ff0000')->andReturn('#ff0000');
        // Red on white doesn't pass AA, so generateAccessibleTextColor is called for light mode
        $colorGenerator->shouldReceive('generateAccessibleTextColor')->with('#ffffff', false, 'AA', false)->andReturn('#000000');

        $themeGenerator = new ThemeGenerator($colorGenerator, $parser);

        $themes = $themeGenerator->generate(['primary' => '#ff0000']);

        expect($themes)->toBe(
            [
            'light' => ['--color-primary' => '#000000'],
            'dark' => ['--color-primary' => '#ff0000'],
            ]
        );
    }
);

it(
    'generates for a specific mode', function () {
        $colorGenerator = Mockery::mock(AccessibleColorGenerator::class);
        $parser = new CssVariableParser();

        $colorGenerator->shouldReceive('getHexFromColorString')->with('#ff0000')->andReturn('#ff0000');
        // Red on white doesn't pass AA, so generateAccessibleTextColor is called for light mode
        $colorGenerator->shouldReceive('generateAccessibleTextColor')->with('#ffffff', false, 'AA', false)->andReturn('#000000');

        $themeGenerator = new ThemeGenerator($colorGenerator, $parser);

        $color = $themeGenerator->generateFor('#ff0000', 'light');

        expect($color)->toBe('#000000');
    }
);

it(
    'validates theme', function () {
        $colorGenerator = Mockery::mock(AccessibleColorGenerator::class);
        $parser = new CssVariableParser();
        $validator = Mockery::mock(ThemeValidator::class);

        $colorGenerator->shouldReceive('getHexFromColorString')->with('#ffffff')->andReturn('#ffffff');
        $colorGenerator->shouldReceive('generateAccessibleTextColor')->andReturn('#000000');
        $validator->shouldReceive('validate')->andReturn(false);

        $themeGenerator = new ThemeGenerator($colorGenerator, $parser, $validator);

        $theme = $themeGenerator->generate(['primary' => '#ffffff']);

        expect($theme)->toBe([]);
    }
);

it(
    'exports to css', function () {
        $colorGenerator = Mockery::mock(AccessibleColorGenerator::class);
        $parser = new CssVariableParser();

        $themeGenerator = new ThemeGenerator($colorGenerator, $parser);

        $theme = [
        'light' => ['--color-primary' => '#000000'],
        'dark' => ['--color-primary' => '#ffffff'],
        ];

        $css = $themeGenerator->export($theme, 'css');

        expect($css)->toContain(':root {');
        expect($css)->toContain('--color-primary: #000000;');
        expect($css)->toContain('@media (prefers-color-scheme: dark) {');
        expect($css)->toContain('--color-primary: #ffffff;');
    }
);

it(
    'exports to json', function () {
        $colorGenerator = Mockery::mock(AccessibleColorGenerator::class);
        $parser = new CssVariableParser();

        $themeGenerator = new ThemeGenerator($colorGenerator, $parser);

        $theme = [
        'light' => ['--color-primary' => '#000000'],
        'dark' => ['--color-primary' => '#ffffff'],
        ];

        $json = $themeGenerator->export($theme, 'json');

        expect($json)->toBe(json_encode($theme, JSON_PRETTY_PRINT));
    }
);
