<?php

use ArtisanPackUI\Accessibility\A11y;

uses();

test( 'returns correct text color for background color', function () {
	$a11y = new A11y();
	expect( $a11y->a11yCSSVarBlackOrWhite( '#63FF1A' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#0003BD' ) )->toEqual( 'white' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#DB95D4' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#DBD739' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#918F26' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#D6444E' ) )->toEqual( 'black' )
														->and( $a11y->a11yCSSVarBlackOrWhite( '#CE414B' ) )->toEqual( 'white' );
} );

test( 'correctly checks the contrast between two colors', function () {
	$a11y = new A11y();
	expect( $a11y->a11yCheckContrastColor( '#D6444E', '#000000' ) )->toBeTrue()
																   ->and( $a11y->a11yCheckContrastColor( '#C94049', '#000000' ) )->toBeFalse()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#000000' ) )->toBeFalse()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#FFFFFF' ) )->toBeTrue()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#262AFF' ) )->toBeFalse()
																   ->and( $a11y->a11yCheckContrastColor( '#2B37C9', '#FF7CE6' ) )->toBeFalse();
} );

test( 'converts hex color to RGB array correctly', function () {
	$a11y = new A11y();
	$reflection = new ReflectionClass( $a11y );
	$method = $reflection->getMethod( 'hexToRgb' );
	$method->setAccessible( true );

	// Pure colors
	expect( $method->invoke( $a11y, '#FF0000' ) )->toEqual( [ 'r' => 255, 'g' => 0, 'b' => 0 ] )
												  ->and( $method->invoke( $a11y, '#00FF00' ) )->toEqual( [ 'r' => 0, 'g' => 255, 'b' => 0 ] )
												  ->and( $method->invoke( $a11y, '#0000FF' ) )->toEqual( [ 'r' => 0, 'g' => 0, 'b' => 255 ] )
												  ->and( $method->invoke( $a11y, '#FFFFFF' ) )->toEqual( [ 'r' => 255, 'g' => 255, 'b' => 255 ] )
												  ->and( $method->invoke( $a11y, '#000000' ) )->toEqual( [ 'r' => 0, 'g' => 0, 'b' => 0 ] );

	// Mixed colors
	expect( $method->invoke( $a11y, '#D6444E' ) )->toEqual( [ 'r' => 214, 'g' => 68, 'b' => 78 ] )
												  ->and( $method->invoke( $a11y, '#2B37C9' ) )->toEqual( [ 'r' => 43, 'g' => 55, 'b' => 201 ] );
} );

test( 'calculates relative luminance according to WCAG 2.0', function () {
	$a11y = new A11y();
	$reflection = new ReflectionClass( $a11y );
	$method = $reflection->getMethod( 'calculateRelativeLuminance' );
	$method->setAccessible( true );

	// White should have luminance of 1
	expect( $method->invoke( $a11y, [ 'r' => 255, 'g' => 255, 'b' => 255 ] ) )->toBeGreaterThan( 0.99 )
																			   ->and( $method->invoke( $a11y, [ 'r' => 255, 'g' => 255, 'b' => 255 ] ) )->toBeLessThanOrEqual( 1.0 );

	// Black should have luminance near 0
	expect( $method->invoke( $a11y, [ 'r' => 0, 'g' => 0, 'b' => 0 ] ) )->toBeLessThan( 0.01 )
																		 ->and( $method->invoke( $a11y, [ 'r' => 0, 'g' => 0, 'b' => 0 ] ) )->toBeGreaterThanOrEqual( 0.0 );

	// Red component should be weighted ~0.2126
	$redLuminance = $method->invoke( $a11y, [ 'r' => 255, 'g' => 0, 'b' => 0 ] );
	expect( $redLuminance )->toBeGreaterThan( 0.20 )
						   ->and( $redLuminance )->toBeLessThan( 0.25 );

	// Green component should be weighted ~0.7152 (highest)
	$greenLuminance = $method->invoke( $a11y, [ 'r' => 0, 'g' => 255, 'b' => 0 ] );
	expect( $greenLuminance )->toBeGreaterThan( 0.70 )
							 ->and( $greenLuminance )->toBeLessThan( 0.75 );

	// Blue component should be weighted ~0.0722 (lowest)
	$blueLuminance = $method->invoke( $a11y, [ 'r' => 0, 'g' => 0, 'b' => 255 ] );
	expect( $blueLuminance )->toBeGreaterThan( 0.05 )
							->and( $blueLuminance )->toBeLessThan( 0.10 );
} );

test( 'calculates contrast ratio according to WCAG 2.0', function () {
	$a11y = new A11y();
	$reflection = new ReflectionClass( $a11y );
	$method = $reflection->getMethod( 'calculateContrastRatio' );
	$method->setAccessible( true );

	// Maximum contrast (black vs white) should be 21:1
	$maxContrast = $method->invoke( $a11y, '#000000', '#FFFFFF' );
	expect( $maxContrast )->toBeGreaterThan( 20.9 )
						  ->and( $maxContrast )->toBeLessThanOrEqual( 21.0 );

	// Minimum contrast (same color) should be 1:1
	expect( $method->invoke( $a11y, '#FF0000', '#FF0000' ) )->toEqual( 1.0 )
															 ->and( $method->invoke( $a11y, '#ABCDEF', '#ABCDEF' ) )->toEqual( 1.0 );

	// Contrast should be symmetrical
	$ratio1 = $method->invoke( $a11y, '#D6444E', '#000000' );
	$ratio2 = $method->invoke( $a11y, '#000000', '#D6444E' );
	expect( $ratio1 )->toEqual( $ratio2 );

	// Verify known contrast ratios from existing tests
	// #D6444E vs #000000 should pass 4.5:1 (test expects true)
	expect( $method->invoke( $a11y, '#D6444E', '#000000' ) )->toBeGreaterThanOrEqual( 4.5 );

	// #C94049 vs #000000 should fail 4.5:1 (test expects false)
	expect( $method->invoke( $a11y, '#C94049', '#000000' ) )->toBeLessThan( 4.5 );

	// #2B37C9 vs #FFFFFF should pass 4.5:1 (test expects true)
	expect( $method->invoke( $a11y, '#2B37C9', '#FFFFFF' ) )->toBeGreaterThanOrEqual( 4.5 );
} );
