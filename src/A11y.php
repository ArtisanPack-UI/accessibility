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
		// hexColor RGB
		$R1 = hexdec( substr( $hexColor, 1, 2 ) );
		$G1 = hexdec( substr( $hexColor, 3, 2 ) );
		$B1 = hexdec( substr( $hexColor, 5, 2 ) );

		// Black RGB
		$blackColor   = "#000000";
		$R2BlackColor = hexdec( substr( $blackColor, 1, 2 ) );
		$G2BlackColor = hexdec( substr( $blackColor, 3, 2 ) );
		$B2BlackColor = hexdec( substr( $blackColor, 5, 2 ) );

		// Calc contrast ratio
		$L1 = 0.2126 * pow( $R1 / 255, 2.2 ) +
			0.7152 * pow( $G1 / 255, 2.2 ) +
			0.0722 * pow( $B1 / 255, 2.2 );

		$L2 = 0.2126 * pow( $R2BlackColor / 255, 2.2 ) +
			0.7152 * pow( $G2BlackColor / 255, 2.2 ) +
			0.0722 * pow( $B2BlackColor / 255, 2.2 );

		$contrastRatio = 0;
		if ( $L1 > $L2 ) {
			$contrastRatio = (float) ( ( $L1 + 0.05 ) / ( $L2 + 0.05 ) );
		} else {
			$contrastRatio = (float) ( ( $L2 + 0.05 ) / ( $L1 + 0.05 ) );
		}

		// If contrast is more than 5, return black color
		if ( $contrastRatio > 4.5 ) {
			return '#000000';
		} else {
			// if not, return white color.
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
		// hexColor RGB
		$R1 = hexdec( substr( $firstHexColor, 1, 2 ) );
		$G1 = hexdec( substr( $firstHexColor, 3, 2 ) );
		$B1 = hexdec( substr( $firstHexColor, 5, 2 ) );

		// Black RGB
		$R2 = hexdec( substr( $secondHexColor, 1, 2 ) );
		$G2 = hexdec( substr( $secondHexColor, 3, 2 ) );
		$B3 = hexdec( substr( $secondHexColor, 5, 2 ) );

		// Calc contrast ratio
		$L1 = 0.2126 * pow( $R1 / 255, 2.2 ) +
			0.7152 * pow( $G1 / 255, 2.2 ) +
			0.0722 * pow( $B1 / 255, 2.2 );

		$L2 = 0.2126 * pow( $R2 / 255, 2.2 ) +
			0.7152 * pow( $G2 / 255, 2.2 ) +
			0.0722 * pow( $B3 / 255, 2.2 );

		$contrastRatio = 0;
		if ( $L1 > $L2 ) {
			$contrastRatio = (float) ( ( $L1 + 0.05 ) / ( $L2 + 0.05 ) );
		} else {
			$contrastRatio = (float) ( ( $L2 + 0.05 ) / ( $L1 + 0.05 ) );
		}

		// If contrast is more than 5, return black color
		if ( $contrastRatio >= 4.5 ) {
			return true;
		}

		return false;
	}
}
