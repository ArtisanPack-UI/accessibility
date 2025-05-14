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
