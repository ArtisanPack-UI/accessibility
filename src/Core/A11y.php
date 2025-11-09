<?php
/**
 * This file is part of the ArtisanPack UI Accessibility package.
 *
 * (c) Jacob Martella <me@jacobmartella.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @since    1.0.0
 * @category Accessibility
 * @package  ArtisanPack\Accessibility
 * @author   Jacob Martella <me@jacobmartella.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link     https://artisanpack.com
 */

namespace ArtisanPack\Accessibility\Core;

use ArtisanPack\Accessibility\Core\Contracts\Config;
use ArtisanPack\Accessibility\Core\Performance\BatchProcessor;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Main accessibility utility class.
 *
 * This class provides methods for determining appropriate text colors
 * based on background colors, checking contrast ratios, and managing
 * accessibility-related user settings.
 *
 * @since    1.0.0
 * @category Accessibility
 * @package  ArtisanPack\Accessibility
 * @author   Jacob Martella <me@jacobmartella.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link     https://artisanpack.com
 */
class A11y
{
    /**
     * The WCAG validator.
     *
     * @var \ArtisanPack\Accessibility\Core\WcagValidator
     */
    private WcagValidator $_wcagValidator;
    private Config $_config;
    private AccessibleColorGenerator $colorGenerator;
    private BatchProcessor $batchProcessor;

    public function __construct(
        Config $config,
        ?WcagValidator $wcagValidator = null,
        ?AccessibleColorGenerator $colorGenerator = null,
        ?BatchProcessor $batchProcessor = null,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        $this->_config = $config;
        $this->_wcagValidator = $wcagValidator ?? new WcagValidator();
        $this->colorGenerator = $colorGenerator ?? new AccessibleColorGenerator($this->_wcagValidator, null, null, $dispatcher);
        $this->batchProcessor = $batchProcessor ?? new BatchProcessor($this->colorGenerator, $this->colorGenerator->getCache());
    }

    /**
     * Returns whether a text color should be black or white based on the background color.
     *
     * Analyzes the provided hex color and determines if black or white text
     * would provide better contrast against it.
     *
     * @since 1.0.0
     *
     * @param  string $hexColor The hex code for the background color.
     * @return string          Either 'black' or 'white' as a string.
     */
    public function a11yCSSVarBlackOrWhite(string $hexColor): string
    {
        return '#000000' === $this->colorGenerator->generateAccessibleTextColor($hexColor) ? 'black' : 'white';
    }

    /**
     * Determines whether black or white text has better contrast against a background color.
     *
     * Calculates the contrast ratio between the background color and both black and white,
     * then returns the hex code for the color (black or white) with better contrast.
     *
     * @since 1.0.0
     *
     * @param  string $hexColor The hex code for the background color.
     * @return string          The hex code for either black (#000000) or white (#FFFFFF).
     */
    public function a11yGetContrastColor(string $hexColor): string
    {
        return $this->colorGenerator->generateAccessibleTextColor($hexColor);
    }

    /**
     * Checks if two colors have sufficient contrast for accessibility.
     *
     * @since 1.0.0
     *
     * @param  string $firstHexColor  The first color to check (hex format).
     * @param  string $secondHexColor The second color to check (hex format).
     * @param  string $level          The WCAG level to check against (e.g., 'AA', 'AAA', 'non-text').
     * @param  bool   $isLargeText    Whether the text is large or not.
     * @return bool True if contrast is sufficient, false otherwise.
     */
    public function a11yCheckContrastColor(string $firstHexColor, string $secondHexColor, string $level = 'aa', bool $isLargeText = false): bool
    {
        try {
            return $this->_wcagValidator->checkContrast($firstHexColor, $secondHexColor, $level, $isLargeText);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function batch(): BatchProcessor
    {
        return $this->batchProcessor;
    }

    /**
     * Get WCAG threshold by level with safe defaults when config is unavailable.
     *
     * @param string $level The WCAG level.
     *
     * @return float
     */
    private function _getWcagThreshold(string $level): float
    {
        $level = strtolower($level);
        $default = $level === 'aaa' ? 7.0 : 4.5;

        $value = $this->_config->get("accessibility.wcag_thresholds.{$level}", $default);
        return is_numeric($value) ? (float) $value : (float) $default;
    }

    /**
     * Get cache size with a safe default when config is unavailable.
     *
     * @return int
     */
    private function _getCacheSize(): int
    {
        $value = $this->_config->get('accessibility.cache_size', 1000);
        return is_numeric($value) ? (int) $value : 1000;
    }
}