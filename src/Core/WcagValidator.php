<?php
/**
 * This file is part of the ArtisanPack UI Accessibility package.
 *
 * (c) Jacob Martella <me@jacobmartella.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @category Accessibility
 * @package  ArtisanPack\Accessibility
 * @author   Jacob Martella <me@jacobmartella.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link     https://artisanpack.com
 * @version  1.0.0
 */

namespace ArtisanPack\Accessibility\Core;

use InvalidArgumentException;

/**
 * Class WcagValidator.
 *
 * @category Accessibility
 * @package  ArtisanPack\Accessibility
 * @author   Jacob Martella <me@jacobmartella.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link     https://artisanpack.com
 */
class WcagValidator
{
    /**
     * The contrast cache.
     *
     * @var array<string, float>
     */
    private array $_contrastCache = [];

    /**
     * Checks if two colors have sufficient contrast for accessibility.
     *
     * @param string $color1      The first color.
     * @param string $color2      The second color.
     * @param string $level       The WCAG level.
     * @param bool   $isLargeText Whether the text is large.
     *
     * @return bool
     */
    public function checkContrast(string $color1, string $color2, string $level = 'AA', bool $isLargeText = false): bool
    {
        $this->_validateHexColor($color1);
        $this->_validateHexColor($color2);

        $ratio = $this->calculateContrastRatio($color1, $color2);

        if ($isLargeText) {
            return match (strtoupper($level)) {
                'AA' => $ratio >= 3,
                'AAA' => $ratio >= 4.5,
                'NON-TEXT' => $ratio >= 3,
                default => false,
            };
        }

        $result = match (strtoupper($level)) {
            'AA' => $ratio >= 4.5,
            'AAA' => $ratio >= 7,
            'NON-TEXT' => $ratio >= 3,
            default => false,
        };

        event(new \ArtisanPack\Accessibility\Events\ColorContrastChecked($color1, $color2, $level, $isLargeText, $result));

        return $result;
    }

    /**
     * Calculates the contrast ratio between two colors.
     *
     * @param string $color1 The first color.
     * @param string $color2 The second color.
     *
     * @return float
     */
    public function calculateContrastRatio(string $color1, string $color2): float
    {
        $colors = [$color1, $color2];
        sort($colors);
        $cacheKey = implode('-', $colors);

        if (isset($this->_contrastCache[$cacheKey])) {
            return $this->_contrastCache[$cacheKey];
        }

        $rgb1 = $this->_hexToRgb($color1);
        $rgb2 = $this->_hexToRgb($color2);

        $l1 = $this->_calculateRelativeLuminance($rgb1);
        $l2 = $this->_calculateRelativeLuminance($rgb2);

        $ratio = ($l1 > $l2) ? (($l1 + 0.05) / ($l2 + 0.05)) : (($l2 + 0.05) / ($l1 + 0.05));

        return $this->_contrastCache[$cacheKey] = $ratio;
    }

    /**
     * Calculates the relative luminance of a color.
     *
     * @param array<string, int> $rgb The color.
     *
     * @return float
     */
    private function _calculateRelativeLuminance(array $rgb): float
    {
        $r = $this->_sRGBtoLin($rgb['r']);
        $g = $this->_sRGBtoLin($rgb['g']);
        $b = $this->_sRGBtoLin($rgb['b']);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Converts an sRGB color component to a linear value.
     *
     * @param float $color The color component.
     *
     * @return float
     */
    private function _sRGBtoLin(float $color): float
    {
        $c = $color / 255;
        if ($c <= 0.03928) {
            return $c / 12.92;
        } else {
            return (($c + 0.055) / 1.055) ** 2.4;
        }
    }

    /**
     * Converts a hex color to an RGB array.
     *
     * @param string $hex The hex color.
     *
     * @return array<string, int>
     */
    private function _hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Validates a hex color.
     *
     * @param string $hex The hex color.
     *
     * @return void
     */
    private function _validateHexColor(string $hex): void
    {
        if (!preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $hex)) {
            throw new InvalidArgumentException("Malformed hex color: {$hex}");
        }
    }
}