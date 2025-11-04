<?php

use ArtisanPack\Accessibility\Core\Theming\CssVariableParser;

it(
    'parses css variable', function () {
        $parser = new CssVariableParser();
        expect($parser->parse('var(--color-primary)'))->toBe('--color-primary');
    }
);

it(
    'returns null for non-css variable', function () {
        $parser = new CssVariableParser();
        expect($parser->parse('#ffffff'))->toBeNull();
    }
);

it(
    'resolves variable from theme', function () {
        $parser = new CssVariableParser();
        $theme = ['--color-primary' => '#000000'];
        expect($parser->resolve('--color-primary', $theme))->toBe('#000000');
    }
);

it(
    'returns null for unresolved variable', function () {
        $parser = new CssVariableParser();
        $theme = ['--color-primary' => '#000000'];
        expect($parser->resolve('--color-secondary', $theme))->toBeNull();
    }
);
