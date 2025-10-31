<?php

use ArtisanPackUI\Accessibility\A11y;

uses(Tests\TestCase::class);

test( 'returns correct text color for background color', function () {
	$a11y = app(A11y::class);
	expect( $a11y->a11yCSSVarBlackOrWhite( '#63FF1A' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#0003BD' ) )->toEqual( 'white' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#DB95D4' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#DBD739' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#918F26' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#D6444E' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#CE414B' ) )->toEqual( 'white' );
} );

test( 'correctly checks the contrast between two colors', function () {
	$a11y = app(A11y::class);
	expect( $a11y->a11yCheckContrastColor( '#D6444E', '#000000' ) )->toBeTrue()
																   ->and( $a11y->a11yCheckContrastColor( '#C94049', '#000000' ) )->toBeFalse()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#000000' ) )->toBeFalse()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#FFFFFF' ) )->toBeTrue()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#262AFF' ) )->toBeFalse()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#FF7CE6' ) )->toBeFalse();
} );

test('handles malformed hex codes in a11yGetContrastColor', function ($malformedHex) {
    $a11y = app(A11y::class);
    // It should return white as it will treat the malformed hex as black
    expect($a11y->a11yGetContrastColor($malformedHex))->toBe('#FFFFFF');
})->with([
    'invalid characters' => '#GHIJKL',
    'wrong length (short)' => '#12345',
    'wrong length (long)' => '#1234567',
    'not a hex' => 'not a hex',
]);

test('handles malformed hex codes in a11yCheckContrastColor', function ($malformedHex) {
    $a11y = app(A11y::class);
    // It will treat the malformed hex as black, so contrast with white is high
    expect($a11y->a11yCheckContrastColor($malformedHex, '#FFFFFF'))->toBeFalse();
    // It will treat the malformed hex as black, so contrast with black is low, so it should be false
    expect($a11y->a11yCheckContrastColor($malformedHex, '#000000'))->toBeFalse();
})->with([
    'invalid characters' => '#GHIJKL',
    'wrong length (short)' => '#12345',
    'wrong length (long)' => '#1234567',
    'not a hex' => 'not a hex',
]);
