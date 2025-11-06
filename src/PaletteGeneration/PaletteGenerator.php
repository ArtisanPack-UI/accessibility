<?php

namespace ArtisanPack\Accessibility\PaletteGeneration;

use ArtisanPack\Accessibility\Core\WcagValidator;
use ArtisanPack\Accessibility\PaletteGeneration\ExportFormats\Exporter;

class PaletteGenerator
{
    private WcagValidator $wcagValidator;

    public function __construct(?WcagValidator $wcagValidator = null)
    {
        $this->wcagValidator = $wcagValidator ?? new WcagValidator();
    }

    public function generatePalette(string $baseColor, int $size = 9): array
    {
        $primaryPalette = $this->generateColorScale($baseColor, $size);
        $secondaryColor = ColorHarmony::complementary($baseColor);
        $secondaryPalette = $this->generateColorScale($secondaryColor, $size);

        return [
            'primary' => $primaryPalette,
            'secondary' => $secondaryPalette,
        ];
    }

    public function generateSemanticPalettes(string $baseColor, int $size = 9): array
    {
        // For simplicity, we'll use a predefined set of base colors for semantic palettes.
        // In a real-world scenario, these could be derived from the base color.
        $semanticColors = [
            'success' => '#28a745',
            'warning' => '#ffc107',
            'error'   => '#dc3545',
            'info'    => '#17a2b8',
        ];

        $semanticPalettes = [];
        foreach ($semanticColors as $name => $color) {
            $semanticPalettes[$name] = $this->generateColorScale($color, $size);
        }

        return $semanticPalettes;
    }

    public function generateColorScale(string $hexColor, int $steps = 9): array
    {
        $hsl = ColorHarmony::hexToHsl($hexColor);
        $scale = [];

        $lightnessStep = (1.0 - $hsl['l']) / (int)($steps / 2);
        $darknessStep = $hsl['l'] / (int)($steps / 2);

        // Generate lighter shades
        for ($i = (int)($steps / 2) - 1; $i >= 0; $i--) {
            $l = $hsl['l'] + ($lightnessStep * ($i + 1));
            $scale[] = ColorHarmony::hslToHex($hsl['h'], $hsl['s'], min(1.0, $l));
        }

        $scale[] = $hexColor;

        // Generate darker shades
        for ($i = 1; $i <= (int)($steps / 2); $i++) {
            $l = $hsl['l'] - ($darknessStep * $i);
            $scale[] = ColorHarmony::hslToHex($hsl['h'], $hsl['s'], max(0.0, $l));
        }

        return $scale;
    }

    public function export(Exporter $exporter, array $palette): string
    {
        return $exporter->export($palette);
    }
}
