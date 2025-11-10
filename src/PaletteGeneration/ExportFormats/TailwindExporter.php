<?php

namespace ArtisanPack\Accessibility\PaletteGeneration\ExportFormats;

class TailwindExporter implements Exporter
{
    public function export(array $palette): string
    {
        $config = [
            \'theme\' => [
                \'extend\' => [
                    \'colors\' => [],
                ],
            ],
        ];

        foreach ($palette as $name => $colors) {
            if (is_array($colors)) {
                $config[\'theme\'][\'extend\'][\'colors\'][$name] = [];
                foreach ($colors as $index => $color) {
                    $config[\'theme\'][\'extend\'][\'colors\'][$name][$index] = $color;
                }
            } else {
                $config[\'theme\'][\'extend\'][\'colors\'][$name] = $colors;
            }
        }

        return "module.exports = " . json_encode($config, JSON_PRETTY_PRINT) . ";";
    }
}