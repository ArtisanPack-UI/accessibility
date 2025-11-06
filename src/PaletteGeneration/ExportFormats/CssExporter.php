<?php

namespace ArtisanPack\Accessibility\PaletteGeneration\ExportFormats;

class CssExporter implements Exporter
{
    public function export(array $palette): string
    {
        $css = ":root {\n";

        foreach ($palette as $name => $colors) {
            if (is_array($colors)) {
                foreach ($colors as $index => $color) {
                    $css .= "    --{$name}-{$index}: {$color};\n";
                }
            } else {
                $css .= "    --{$name}: {$colors};\n";
            }
        }

        $css .= "}";

        return $css;
    }
}