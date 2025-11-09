<?php
/**
 * Accessible Color Generator
 *
 * Provides methods for generating accessible text colors based on a background color.
 *
 * @since   1.0.0
 * @package ArtisanPack\Accessibility
 */

namespace ArtisanPack\Accessibility\Core;

use ArtisanPack\Accessibility\Core\Caching\ArrayCache;
use ArtisanPack\Accessibility\Core\Caching\CacheManager;
use ArtisanPack\Accessibility\Core\Events\CacheHit;
use ArtisanPack\Accessibility\Core\Events\CacheMiss;
use ArtisanPack\Accessibility\Core\Theming\CssVariableParser;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Generates accessible text colors.
 *
 * This class provides methods to determine an appropriate text color with sufficient
 * contrast against a given background color, which can be provided as either a
 * hex code or a Tailwind CSS color name.
 *
 * @since 1.0.0
 */
class AccessibleColorGenerator
{
    private const RGB_MAX = 255;
    private const RGB_MIN = 0;

    protected WcagValidator $wcagValidator;
    protected CssVariableParser $parser;
    protected CacheInterface $cache;
    protected ?EventDispatcherInterface $dispatcher;
    private ?array $tailwindColors = null;

    public function __construct(WcagValidator $wcagValidator = null, CssVariableParser $parser = null, CacheManager $cacheManager = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->wcagValidator = $wcagValidator ?? new WcagValidator();
        $this->parser = $parser ?? new CssVariableParser();
        $this->cache = $cacheManager ? $cacheManager->store() : new ArrayCache();
        $this->dispatcher = $dispatcher;
    }

    /**
     * Generates an accessible text color for a given background color.
     *
     * This method determines the best-contrasting text color. It can return
     * either black or white, or it can generate a lighter/darker shade of
     * the original background color that meets accessibility standards.
     *
     * @since 1.0.0
     *
     * @param  string $backgroundColor The background color. Can be a hex code (e.g., '#3b82f6')
     *                                 or a Tailwind color name (e.g., 'blue-500').
     * @param  bool   $tint            Optional. If true, generates an accessible tint or shade.
     *                                 If false, returns black or white. Default false.
     * @return string                 The generated accessible hex color string.
     */
    public function generateAccessibleTextColor(string $backgroundColor, bool $tint = false, string $level = 'AA', bool $isLargeText = false): string
    {
        $hexColor = $this->getHexFromColorString($backgroundColor);

        if (!$hexColor) {
            return '#000000';
        }

        $cacheKey = $this->getCacheKey($hexColor, $tint, $level, $isLargeText);
        if ($this->cache->has($cacheKey)) {
            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new CacheHit($cacheKey));
            }
            return $this->cache->get($cacheKey);
        }

        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CacheMiss($cacheKey));
        }

        if ($tint) {
            $result = $this->findClosestAccessibleShade($hexColor, $level, $isLargeText);
            $this->cache->set($cacheKey, $result);
            return $result;
        }

        $blackContrast = $this->wcagValidator->calculateContrastRatio($hexColor, '#000000');
        $whiteContrast = $this->wcagValidator->calculateContrastRatio($hexColor, '#FFFFFF');

        $result = $blackContrast > $whiteContrast ? '#000000' : '#FFFFFF';
        $this->cache->set($cacheKey, $result);

        return $result;
    }

    public function getCacheKey(string $hexColor, bool $tint, string $level, bool $isLargeText): string
    {
        if ($tint) {
            return "shade.{$hexColor}-{$level}-" . ($isLargeText ? 'large' : 'normal');
        }

        return "bw.{$hexColor}";
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function fromTheme(string $cssValue, array $theme, string $mode = 'light'): string
    {
        $variableName = $this->parser->parse($cssValue);

        if ($variableName) {
            $color = $this->parser->resolve($variableName, $theme[$mode]);
        } else {
            $color = $cssValue;
        }

        return $this->generateAccessibleTextColor($color);
    }

    /**
     * Converts a color string (Tailwind or hex) to a hex code.
     *
     * @since 1.0.0
     *
     * @param  string $colorString The color string to process.
     * @return string|null         The hex color string or null if not found.
     */
    public function getHexFromColorString( string $colorString ): ?string
    {
        $colorString = strtolower(trim($colorString));

        // Check if it's already a hex color.
        if (preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/', $colorString) ) {
            return $colorString;
        }

        // Check if it's an rgb color
        if (strpos($colorString, 'rgb') === 0) {
            preg_match('/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/', $colorString, $matches);
            if (count($matches) === 4) {
                return $this->rgbToHex((int)$matches[1], (int)$matches[2], (int)$matches[3]);
            }
        }

        // Check if it's an hsl color
        if (strpos($colorString, 'hsl') === 0) {
            preg_match('/hsl\(\s*(\d+)\s*,\s*(\d+)%\s*,\s*(\d+)%\s*\)/', $colorString, $matches);
            if (count($matches) === 4) {
                return $this->hslToHex((int)$matches[1], (int)$matches[2], (int)$matches[3]);
            }
        }

        // Check if it's a known Tailwind color.
        if ($this->tailwindColors === null) {
            $this->tailwindColors = require __DIR__ . '/../../resources/tailwind-colors.php';
        }

        return $this->tailwindColors[ $colorString ] ?? null;
    }

    protected function rgbToHex(int $r, int $g, int $b): string
    {
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }

    protected function hslToHex(int $h, int $s, int $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        $r = $g = $b = 0;

        if (0 <= $h && $h < 60) {
            $r = $c;
            $g = $x;
        } else if (60 <= $h && $h < 120) {
            $r = $x;
            $g = $c;
        } else if (120 <= $h && $h < 180) {
            $g = $c;
            $b = $x;
        } else if (180 <= $h && $h < 240) {
            $g = $x;
            $b = $c;
        } else if (240 <= $h && $h < 300) {
            $r = $x;
            $b = $c;
        } else if (300 <= $h && $h < 360) {
            $r = $c;
            $b = $x;
        }

        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);

        return $this->rgbToHex($r, $g, $b);
    }

    private function isDark(string $hexColor): bool
    {
        $hex = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $luminance = (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);

        return $luminance < 0.5;
    }

    /**
     * Finds the closest accessible tint or shade of a base color.
     *
     * Iteratively lightens and darkens the base color until a variant with
     * sufficient contrast (4.5:1) is found. If no variant is found, it
     * falls back to black or white.
     *
     * @since 1.0.0
     *
     * @param  string $baseHex     The hex color to find a variant for.
     * @param  string $level       The WCAG level to check against.
     * @param  bool   $isLargeText Whether the text is large or not.
     * @return string          The accessible hex color variant.
     */
    protected function findClosestAccessibleShade(string $baseHex, string $level = 'AA', bool $isLargeText = false): string
    {
        $cacheKey = $this->getCacheKey($baseHex, true, $level, $isLargeText);

        if ($this->cache->has($cacheKey)) {
            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new CacheHit($cacheKey));
            }
            return $this->cache->get($cacheKey);
        }

        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CacheMiss($cacheKey));
        }

        $low = 0;
        $high = 100;
        $closestColor = null;

        // Decide whether to search for a lighter or darker color first
        $isDark = $this->isDark($baseHex);

        // Search for lighter shades
        while ($low <= $high) {
            $mid = (int)(($low + $high) / 2);
            $factor = $mid / 100.0;
            $color = $this->adjustBrightness($baseHex, $isDark ? $factor : -$factor);

            if ($this->wcagValidator->checkContrast($baseHex, $color, $level, $isLargeText)) {
                $closestColor = $color;
                $high = $mid - 1;
            } else {
                $low = $mid + 1;
            }
        }

        if ($closestColor) {
            $this->cache->set($cacheKey, $closestColor);
            return $closestColor;
        }
        
        // Fallback to the other direction if no color is found
        $low = 0;
        $high = 100;
        
        while ($low <= $high) {
            $mid = (int)(($low + $high) / 2);
            $factor = $mid / 100.0;
            $color = $this->adjustBrightness($baseHex, $isDark ? -$factor : $factor);

            if ($this->wcagValidator->checkContrast($baseHex, $color, $level, $isLargeText)) {
                $closestColor = $color;
                $high = $mid - 1;
            } else {
                $low = $mid + 1;
            }
        }
        
        if ($closestColor) {
            $this->cache->set($cacheKey, $closestColor);
            return $closestColor;
        }

        $blackContrast = $this->wcagValidator->calculateContrastRatio($baseHex, '#000000');
        $whiteContrast = $this->wcagValidator->calculateContrastRatio($baseHex, '#FFFFFF');

        $result = $blackContrast > $whiteContrast ? '#000000' : '#FFFFFF';
        $this->cache->set($cacheKey, $result);

        return $result;
    }
    /**
     * Increases or decreases the brightness of a hex color.
     *
     * @since 1.0.0
     *
     * @param  string $hex    The hex color string.
     * @param  float  $factor The brightness factor. Positive for lighter, negative for darker.
     *                        e.g., 0.1 for 10% lighter, -0.2 for 20% darker.
     * @return string        The new hex color string.
     */
    protected function adjustBrightness( string $hex, float $factor ): string
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = round(max(self::RGB_MIN, min(self::RGB_MAX, $r + ( self::RGB_MAX * $factor ))));
        $g = round(max(self::RGB_MIN, min(self::RGB_MAX, $g + ( self::RGB_MAX * $factor ))));
        $b = round(max(self::RGB_MIN, min(self::RGB_MAX, $b + ( self::RGB_MAX * $factor ))));

        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
        . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
        . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}