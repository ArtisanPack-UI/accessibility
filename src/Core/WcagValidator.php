<?php

namespace ArtisanPack\Accessibility\Core;

use InvalidArgumentException;

class WcagValidator
{
    private const LUMINANCE_RED_COEFFICIENT = 0.2126;
    private const LUMINANCE_GREEN_COEFFICIENT = 0.7152;
    private const LUMINANCE_BLUE_COEFFICIENT = 0.0722;
    private const RGB_MAX = 255;

    private static array $contrastCache = [];

    public function checkContrast(string $color1, string $color2, string $level = 'AA', bool $isLargeText = false): bool
    {
        $this->validateHexColor($color1);
        $this->validateHexColor($color2);

        $ratio = $this->calculateContrastRatio($color1, $color2);

        if ($isLargeText) {
            return match (strtoupper($level)) {
                'AA' => $ratio >= 3,
                'AAA' => $ratio >= 4.5,
                'NON-TEXT' => $ratio >= 3,
                default => false,
            };
        }

        return match (strtoupper($level)) {
            'AA' => $ratio >= 4.5,
            'AAA' => $ratio >= 7,
            'NON-TEXT' => $ratio >= 3,
            default => false,
        };
    }

    public function calculateContrastRatio(string $color1, string $color2): float
    {
        $colors = [$color1, $color2];
        sort($colors);
        $cacheKey = implode('-', $colors);

        if (isset(self::$contrastCache[$cacheKey])) {
            return self::$contrastCache[$cacheKey];
        }

        $rgb1 = $this->hexToRgb($color1);
        $rgb2 = $this->hexToRgb($color2);

        $l1 = $this->calculateRelativeLuminance($rgb1);
        $l2 = $this->calculateRelativeLuminance($rgb2);

        $ratio = ($l1 > $l2) ? (($l1 + 0.05) / ($l2 + 0.05)) : (($l2 + 0.05) / ($l1 + 0.05));

        return self::$contrastCache[$cacheKey] = $ratio;
    }

    private function calculateRelativeLuminance(array $rgb): float
    {
        $r = $this->sRGBtoLin($rgb['r']);
        $g = $this->sRGBtoLin($rgb['g']);
        $b = $this->sRGBtoLin($rgb['b']);

        return self::LUMINANCE_RED_COEFFICIENT * $r + self::LUMINANCE_GREEN_COEFFICIENT * $g + self::LUMINANCE_BLUE_COEFFICIENT * $b;
    }

    private function sRGBtoLin($color_channel): float
    {
        $c = $color_channel / self::RGB_MAX;
        if ($c <= 0.03928) {
            return $c / 12.92;
        } else {
            return (($c + 0.055) / 1.055) ** 2.4;
        }
    }

    private function hexToRgb(string $hexColor): array
    {
        $hex = ltrim($hexColor, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private function validateHexColor(string $hexColor): void
    {
        if (!preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $hexColor)) {
            throw new InvalidArgumentException("Malformed hex color: {$hexColor}");
        }
    }
}
