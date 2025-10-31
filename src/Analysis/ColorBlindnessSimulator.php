<?php

namespace ArtisanPackUI\Accessibility\Analysis;

class ColorBlindnessSimulator
{
    // Protanopia simulation matrix
    private const PROTANOPIA_MATRIX = [
        [0.567, 0.433, 0],
        [0.558, 0.442, 0],
        [0, 0.242, 0.758]
    ];

    // Deuteranopia simulation matrix
    private const DEUTERANOPIA_MATRIX = [
        [0.625, 0.375, 0],
        [0.7, 0.3, 0],
        [0, 0.3, 0.7]
    ];

    // Tritanopia simulation matrix
    private const TRITANOPIA_MATRIX = [
        [0.95, 0.05, 0],
        [0, 0.433, 0.567],
        [0, 0.475, 0.525]
    ];

    public function simulateProtanopia(string $hexColor): string
    {
        return $this->simulate($hexColor, self::PROTANOPIA_MATRIX);
    }

    public function simulateDeuteranopia(string $hexColor): string
    {
        return $this->simulate($hexColor, self::DEUTERANOPIA_MATRIX);
    }

    public function simulateTritanopia(string $hexColor): string
    {
        return $this->simulate($hexColor, self::TRITANOPIA_MATRIX);
    }

    private function simulate(string $hexColor, array $matrix): string
    {
        $rgb = $this->hexToRgb($hexColor);

        $r = $rgb['r'];
        $g = $rgb['g'];
        $b = $rgb['b'];

        $r_sim = $r * $matrix[0][0] + $g * $matrix[0][1] + $b * $matrix[0][2];
        $g_sim = $r * $matrix[1][0] + $g * $matrix[1][1] + $b * $matrix[1][2];
        $b_sim = $r * $matrix[2][0] + $g * $matrix[2][1] + $b * $matrix[2][2];

        return $this->rgbToHex((int)round($r_sim), (int)round($g_sim), (int)round($b_sim));
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

    public function simulateBlurredVision(string $hexColor, int $level = 1): string
    {
        $rgb = $this->hexToRgb($hexColor);
        $grey = 128;

        $r = (int) round($rgb['r'] * (1 - $level * 0.1) + $grey * $level * 0.1);
        $g = (int) round($rgb['g'] * (1 - $level * 0.1) + $grey * $level * 0.1);
        $b = (int) round($rgb['b'] * (1 - $level * 0.1) + $grey * $level * 0.1);

        return $this->rgbToHex($r, $g, $b);
    }    private function rgbToHex(int $r, int $g, int $b): string
    {
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}
