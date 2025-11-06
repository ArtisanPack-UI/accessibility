<?php

namespace ArtisanPack\Accessibility\PaletteGeneration\ExportFormats;

class JsonExporter implements Exporter
{
    public function export(array $palette): string
    {
        return json_encode($palette, JSON_PRETTY_PRINT);
    }
}