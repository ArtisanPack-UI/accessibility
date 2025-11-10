<?php

namespace ArtisanPack\Accessibility\PaletteGeneration;

class ColorHarmony
{
	/**
	 * Finds the complementary color.
	 *
	 * @param string $hexColor
	 * @return string
	 */
	public static function complementary( string $hexColor ): string
	{
		$hsl      = self::hexToHsl( $hexColor );
		$hsl['h'] = ( $hsl['h'] + 180 ) % 360;
		return self::hslToHex( $hsl['h'], $hsl['s'], $hsl['l'] );
	}

	/**
	 * Converts a hex color to HSL.
	 *
	 * @param string $hex
	 * @return array<string, float>
	 */
	public static function hexToHsl( string $hex ): array
	{
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

		$max = max( $r, $g, $b );
		$min = min( $r, $g, $b );

		$h = 0;
		$s = 0;
		$l = ( $max + $min ) / 2;

		if ( $max === $min ) {
			$h = $s = 0;
		} else {
			$diff = $max - $min;
			$s    = $l > 0.5 ? $diff / ( 2 - $max - $min ) : $diff / ( $max + $min );

			switch ( $max ) {
				case $r:
					$h = ( $g - $b ) / $diff + ( $g < $b ? 6 : 0 );
					break;
				case $g:
					$h = ( $b - $r ) / $diff + 2;
					break;
				case $b:
					$h = ( $r - $g ) / $diff + 4;
					break;
			}

			$h /= 6;
		}

		return [ 'h' => $h * 360, 's' => $s, 'l' => $l ];
	}

	/**
	 * Converts an HSL color to hex.
	 *
	 * @param float $h
	 * @param float $s
	 * @param float $l
	 * @return string
	 */
	public static function hslToHex( float $h, float $s, float $l ): string
	{
		$h /= 360;
		$r = $l;
		$g = $l;
		$b = $l;

		$v = ( $l <= 0.5 ) ? ( $l * ( 1.0 + $s ) ) : ( $l + $s - $l * $s );

		if ( $v > 0 ) {
			$m       = $l + $l - $v;
			$sv      = ( $v - $m ) / $v;
			$h       *= 6.0;
			$sextant = floor( $h );
			$fract   = $h - $sextant;
			$vsf     = $v * $sv * $fract;
			$mid1    = $m + $vsf;
			$mid2    = $v - $vsf;

			switch ( $sextant ) {
				case 0:
					$r = $v;
					$g = $mid1;
					$b = $m;
					break;
				case 1:
					$r = $mid2;
					$g = $v;
					$b = $m;
					break;
				case 2:
					$r = $m;
					$g = $v;
					$b = $mid1;
					break;
				case 3:
					$r = $m;
					$g = $mid2;
					$b = $v;
					break;
				case 4:
					$r = $mid1;
					$g = $m;
					$b = $v;
					break;
				case 5:
					$r = $v;
					$g = $m;
					$b = $mid2;
					break;
			}
		}

		$r = round( $r * 255 );
		$g = round( $g * 255 );
		$b = round( $b * 255 );

		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) . str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) . str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}

	/**
	 * Finds the triadic colors.
	 *
	 * @param string $hexColor
	 * @return array<int, string>
	 */
	public static function triadic( string $hexColor ): array
	{
		$hsl = self::hexToHsl( $hexColor );
		$h   = $hsl['h'];

		$h1 = ( $h + 120 ) % 360;
		$h2 = ( $h + 240 ) % 360;

		return [
			self::hslToHex( $h1, $hsl['s'], $hsl['l'] ),
			self::hslToHex( $h2, $hsl['s'], $hsl['l'] ),
		];
	}

	/**
	 * Finds the analogous colors.
	 *
	 * @param string $hexColor
	 * @return array<int, string>
	 */
	public static function analogous( string $hexColor ): array
	{
		$hsl = self::hexToHsl( $hexColor );
		$h   = $hsl['h'];

		$h1 = ( $h + 30 ) % 360;
		$h2 = ( $h - 30 + 360 ) % 360;

		return [
			self::hslToHex( $h1, $hsl['s'], $hsl['l'] ),
			self::hslToHex( $h2, $hsl['s'], $hsl['l'] ),
		];
	}

	/**
	 * Finds the split complementary colors.
	 *
	 * @param string $hexColor
	 * @return array<int, string>
	 */
	public static function splitComplementary( string $hexColor ): array
	{
		$hsl = self::hexToHsl( $hexColor );
		$h   = $hsl['h'];

		$h1 = ( $h + 150 ) % 360;
		$h2 = ( $h + 210 ) % 360;

		return [
			self::hslToHex( $h1, $hsl['s'], $hsl['l'] ),
			self::hslToHex( $h2, $hsl['s'], $hsl['l'] ),
		];
	}
}