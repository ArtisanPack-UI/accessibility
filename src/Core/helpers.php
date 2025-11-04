<?php
/**
 * Accessibility Helper Functions
 *
 * Provides global helper functions for accessibility-related functionality.
 * These functions serve as convenient wrappers around the A11y class methods.
 *
 * @since   1.0.0
 * @package ArtisanPack\Accessibility
 */

use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;

if (! function_exists('a11y') ) {
    /**
     * Get the A11y instance from the service container.
     *
     * Provides a convenient way to access the A11y service throughout
     * the application without needing to use dependency injection.
     *
     * @since 1.0.0
     *
     * @return A11y The A11y service instance.
     */
    function a11y()
    {
        return app('a11y');
    }
}

if (! function_exists('a11yCSSVarBlackOrWhite') ) {
    /**
     * Returns whether a text color should be black or white based on the background color.
     *
     * Analyzes the provided hex color and determines if black or white text
     * would provide better contrast against it. This is a helper function that
     * calls the corresponding method on the A11y class.
     *
     * @since 1.0.0
     *
     * @param  string $hexColor The hex code for the background color.
     * @return string          Either 'black' or 'white' as a string.
     */
    function a11yCSSVarBlackOrWhite( string $hexColor ): string
    {
        return a11y()->a11yCSSVarBlackOrWhite($hexColor);
    }
}

if (! function_exists('a11yGetContrastColor') ) {
    /**
     * Determines whether black or white text has better contrast against a background color.
     *
     * Calculates the contrast ratio between the background color and both black and white,
     * then returns the hex code for the color (black or white) with better contrast.
     * This is a helper function that calls the corresponding method on the A11y class.
     *
     * @since 1.0.0
     *
     * @param  string $hexColor The hex code for the background color.
     * @return string          The hex code for either black (#000000) or white (#FFFFFF).
     */
    function a11yGetContrastColor( string $hexColor ): string
    {
        return a11y()->a11yGetContrastColor($hexColor);
    }
}



if (! function_exists('a11yCheckContrastColor') ) {
    /**
     * Checks if two colors have sufficient contrast for accessibility.
     *
     * Calculates the contrast ratio between two colors according to WCAG 2.0 guidelines.
     * Returns true if the contrast ratio is at least 4.5:1, which is the minimum
     * recommended for normal text to be considered accessible.
     * This is a helper function that calls the corresponding method on the A11y class.
     *
     * @since 1.0.0
     *
     * @param  string $firstHexColor  The first color to check (hex format).
     * @param  string $secondHexColor The second color to check (hex format).
     * @return bool                  True if contrast is sufficient (≥4.5:1), false otherwise.
     */
    function a11yCheckContrastColor( string $firstHexColor, string $secondHexColor ): bool
    {
        return a11y()->a11yCheckContrastColor($firstHexColor, $secondHexColor);
    }
}

if (! function_exists('generateAccessibleTextColor') ) {
    /**
     * Generates an accessible text color for a given background color.
     *
     * This function determines the best-contrasting text color. It can return
     * either black or white, or it can generate a lighter/darker shade of
     * the original background color that meets accessibility standards.
     * This is a helper function that creates an instance of AccessibleColorGenerator
     * and calls its generateAccessibleTextColor method.
     *
     * @since 1.0.0
     *
     * @param  string $backgroundColor The background color. Can be a hex code (e.g., '#3b82f6')
     *                                 or a Tailwind color name (e.g., 'blue-500').
     * @param  bool   $tint            Optional. If true, generates an accessible tint or shade.
     *                                 If false, returns black or white. Default false.
     * @return string                 The generated accessible hex color string.
     */
    function generateAccessibleTextColor( string $backgroundColor, bool $tint = false ): string
    {
        return ( new AccessibleColorGenerator() )->generateAccessibleTextColor($backgroundColor, $tint);
    }
}
