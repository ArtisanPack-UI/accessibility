<?php

uses(Tests\TestCase::class);

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;

test(
    'handles malformed hex codes', function ($malformedHex) {
        $generator = new AccessibleColorGenerator();
        expect($generator->generateAccessibleTextColor($malformedHex))->toBe('#000000');
    }
)->with(
    [
        'invalid characters' => '#GHIJKL',
        'wrong length (short)' => '#12345',
        'wrong length (long)' => '#1234567',
        'not a hex' => 'not a hex',
        ]
);

test(
    'handles invalid Tailwind color names', function ($invalidTailwind) {
        $generator = new AccessibleColorGenerator();
        expect($generator->generateAccessibleTextColor($invalidTailwind))->toBe('#000000');
    }
)->with(
    [
        'non-existent color' => 'blue-1000',
        'misspelled color' => 'blu-500',
        ]
);

test(
    'handles empty string input', function () {
        $generator = new AccessibleColorGenerator();
        expect($generator->generateAccessibleTextColor(''))->toBe('#000000');
    }
);

test(
    'is case-insensitive for hex codes', function ($hex) {
        $generator = new AccessibleColorGenerator();
        $resultForLowerCase = $generator->generateAccessibleTextColor(strtolower($hex));
        $resultForUpperCase = $generator->generateAccessibleTextColor(strtoupper($hex));
        expect($resultForLowerCase)->toBe($resultForUpperCase);
    }
)->with(
    [
        '#ffffff',
        '#ff0000',
        '#00ff00',
        ]
);

test(
    'is case-insensitive for Tailwind colors', function ($tailwindColor) {
        $generator = new AccessibleColorGenerator();
        $resultForLowerCase = $generator->generateAccessibleTextColor(strtolower($tailwindColor));
        $resultForUpperCase = $generator->generateAccessibleTextColor(strtoupper($tailwindColor));
        expect($resultForLowerCase)->toBe($resultForUpperCase);
    }
)->with(
    [
        'blue-500',
        'RED-500',
        'Green-500',
        ]
);
