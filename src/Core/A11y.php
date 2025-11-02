<?php
/**
 * Accessibility Utility Class
 *
 * Provides utility methods for accessibility-related functionality,
 * including color contrast checking and text color determination.
 *
 * @since      1.0.0
 * @package    ArtisanPack\Accessibility
 */

namespace ArtisanPack\Accessibility\Core;

use InvalidArgumentException;

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
    private WcagValidator $wcagValidator;
    private Config $config;

    public function __construct(Config $config, WcagValidator $wcagValidator = null)
    {
        $this->config = $config;
        $this->wcagValidator = $wcagValidator ?? new WcagValidator();
    }

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
    public function a11yCSSVarBlackOrWhite(string $hexColor): string
    {
        return '#000000' === $this->a11yGetContrastColor($hexColor) ? 'black' : 'white';
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
    public function a11yGetContrastColor(string $hexColor): string
    {
        $blackContrast = $this->wcagValidator->calculateContrastRatio($hexColor, '#000000');
        $whiteContrast = $this->wcagValidator->calculateContrastRatio($hexColor, '#FFFFFF');

        return $blackContrast > $whiteContrast ? '#000000' : '#FFFFFF';
    }

    /**
     * Checks if two colors have sufficient contrast for accessibility.
     *
     * @since 1.0.0
     *
     * @param string $firstHexColor The first color to check (hex format).
     * @param string $secondHexColor The second color to check (hex format).
     * @param string $level The WCAG level to check against (e.g., 'AA', 'AAA', 'non-text').
     * @param bool $isLargeText Whether the text is large or not.
     * @return bool True if contrast is sufficient, false otherwise.
     */
    public function a11yCheckContrastColor(string $firstHexColor, string $secondHexColor, string $level = 'aa', bool $isLargeText = false): bool
    {
        try {
            return $this->wcagValidator->checkContrast($firstHexColor, $secondHexColor, $level, $isLargeText);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Get WCAG threshold by level with safe defaults when config is unavailable.
     */
    private function getWcagThreshold(string $level): float
    {
        $level = strtolower($level);
        $default = $level === 'aaa' ? 7.0 : 4.5;

        $value = $this->config->get("accessibility.wcag_thresholds.{$level}", $default);
        return is_numeric($value) ? (float) $value : (float) $default;
    }

    /**
     * Get cache size with a safe default when config is unavailable.
     */
    private function getCacheSize(): int
    {
        $value = $this->config->get('accessibility.cache_size', 1000);
        return is_numeric($value) ? (int) $value : 1000;
    }
}