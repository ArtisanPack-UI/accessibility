<?php
/**
 * Accessibility Utility Class
 *
 * Provides utility methods for accessibility-related functionality,
 * including color contrast checking and text color determination.
 *
 * @since      1.0.0
 * @package    ArtisanPackUI\Accessibility
 */

namespace ArtisanPackUI\Accessibility;

/**
 * Main accessibility utility class.
 *
 * This class provides methods for determining appropriate text colors
 * based on background colors, checking contrast ratios, and managing
 * accessibility-related user settings.
 *
 * @since 1.0.0
 */
class A11y
{
	/**
	 * Returns whether a text color should be black or white based on the background color.
	 *
	 * Analyzes the provided hex color and determines if black or white text
	 * would provide better contrast against it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hexColor The hex code for the background color.
	 * @return string          Either 'black' or 'white' as a string.
	 */
	public function a11yCSSVarBlackOrWhite( string $hexColor ): string
	{
		if ( '#000000' === $this->a11yGetContrastColor( $hexColor ) ) {
			return 'black';
		} else {
			return 'white';
		}
	}

	/**
	 * Determines whether black or white text has better contrast against a background color.
	 *
	 * Calculates the contrast ratio between the background color and both black and white,
	 * then returns the hex code for the color (black or white) with better contrast.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hexColor The hex code for the background color.
	 * @return string          The hex code for either black (#000000) or white (#FFFFFF).
	 */
	public function a11yGetContrastColor( string $hexColor ): string
	{
		$blackContrastRatio = $this->calculateContrastRatio( $hexColor, '#000000' );

		if ( $blackContrastRatio > 4.5 ) {
			return '#000000';
		} else {
			return '#FFFFFF';
		}
	}

	/**
	 * Gets the user's setting for how long the toast element should stay on the screen.
	 *
	 * Retrieves the user's preference for toast notification duration from their settings.
	 * If no setting is found, defaults to 5 seconds. The value is returned in milliseconds.
	 *
	 * @since 1.0.0
	 *
	 * @return float|int The toast duration in milliseconds.
	 */
	public function getToastDuration(): float|int
	{
		$user = auth()->user();
		return $user->getSetting( 'a11y-toast-duration', 5 ) * 1000;
	}

	/**
	 * Checks if two colors have sufficient contrast for accessibility.
	 *
	 * Calculates the contrast ratio between two colors according to WCAG 2.0 guidelines.
	 * Returns true if the contrast ratio is at least 4.5:1, which is the minimum
	 * recommended for normal text to be considered accessible.
	 *
	 * @since 1.0.0
	 *
	 * @param string $firstHexColor  The first color to check (hex format).
	 * @param string $secondHexColor The second color to check (hex format).
	 * @return bool                  True if contrast is sufficient (≥4.5:1), false otherwise.
	 */
	public function a11yCheckContrastColor( string $firstHexColor, string $secondHexColor ): bool
	{
		$contrastRatio = $this->calculateContrastRatio( $firstHexColor, $secondHexColor );

		return $contrastRatio >= 4.5;
	}

	/**
	 * Converts a hex color string to an RGB array.
	 *
	 * Takes a hex color code (e.g., #FF0000) and extracts the red, green, and blue
	 * components as integer values (0-255).
	 *
	 * @since 1.0.0
	 *
	 * @param string $hexColor The hex color code to convert (e.g., #FF0000).
	 * @return array{r: int, g: int, b: int} Associative array with 'r', 'g', and 'b' keys.
	 */
	private function hexToRgb( string $hexColor ): array
	{
		return [
			'r' => hexdec( substr( $hexColor, 1, 2 ) ),
			'g' => hexdec( substr( $hexColor, 3, 2 ) ),
			'b' => hexdec( substr( $hexColor, 5, 2 ) ),
		];
	}

	/**
	 * Calculates the relative luminance of a color according to WCAG 2.0.
	 *
	 * Implements the relative luminance formula from WCAG 2.0 specification.
	 * The result is a value between 0 (darkest) and 1 (lightest).
	 *
	 * Formula: L = 0.2126 * R + 0.7152 * G + 0.0722 * B (where RGB are normalized and gamma corrected)
	 *
	 * @since 1.0.0
	 * @link https://www.w3.org/TR/WCAG20-TECHS/G17.html WCAG 2.0 Relative Luminance
	 *
	 * @param array{r: int, g: int, b: int} $rgb RGB color array with values 0-255.
	 * @return float The relative luminance value (0-1).
	 */
	private function calculateRelativeLuminance( array $rgb ): float
	{
		return 0.2126 * pow( $rgb['r'] / 255, 2.2 ) +
			0.7152 * pow( $rgb['g'] / 255, 2.2 ) +
			0.0722 * pow( $rgb['b'] / 255, 2.2 );
	}

	/**
	 * Calculates the contrast ratio between two colors according to WCAG 2.0.
	 *
	 * Implements the contrast ratio formula from WCAG 2.0 specification.
	 * The contrast ratio is expressed as a ratio ranging from 1:1 (no contrast) to 21:1 (maximum contrast).
	 *
	 * WCAG 2.0 guidelines require:
	 * - AA level (normal text): minimum 4.5:1
	 * - AA level (large text): minimum 3:1
	 * - AAA level (normal text): minimum 7:1
	 * - AAA level (large text): minimum 4.5:1
	 *
	 * @since 1.0.0
	 * @link https://www.w3.org/TR/WCAG20-TECHS/G18.html WCAG 2.0 Contrast Ratio
	 *
	 * @param string $color1 The first color to compare (hex format, e.g., #FF0000).
	 * @param string $color2 The second color to compare (hex format, e.g., #FFFFFF).
	 * @return float The contrast ratio between the two colors (1-21).
	 */
	private function calculateContrastRatio( string $color1, string $color2 ): float
	{
		$rgb1 = $this->hexToRgb( $color1 );
		$rgb2 = $this->hexToRgb( $color2 );

		$L1 = $this->calculateRelativeLuminance( $rgb1 );
		$L2 = $this->calculateRelativeLuminance( $rgb2 );

		if ( $L1 > $L2 ) {
			return (float) ( ( $L1 + 0.05 ) / ( $L2 + 0.05 ) );
		} else {
			return (float) ( ( $L2 + 0.05 ) / ( $L1 + 0.05 ) );
		}
	}
}
