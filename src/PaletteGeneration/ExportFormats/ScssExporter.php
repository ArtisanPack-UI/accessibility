<?php

namespace ArtisanPack\Accessibility\PaletteGeneration\ExportFormats;

class ScssExporter implements Exporter
{
    public function export(array $palette): string
    {
        $scss = "";

        foreach ($palette as $name => $colors) {
            if (is_array($colors)) {
                foreach ($colors as $index => $color) {
                    $scss .= "\${$name}-{$index}: {$color};\n";
                }
            } else {
                $scss .= "\${$name}: {$colors};\n";
            }
        }

        return $scss;
    }
}