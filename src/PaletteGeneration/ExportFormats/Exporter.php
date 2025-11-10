<?php

namespace ArtisanPack\Accessibility\PaletteGeneration\ExportFormats;

interface Exporter
{
    public function export(array $palette): string;
}
