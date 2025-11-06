# Advanced Color Palette Generation

This document provides a guide on how to use the advanced color palette generation tools.

## Generating a Palette

To generate a color palette, you can use the `PaletteGenerator` class. This class allows you to generate a primary and secondary color palette from a base color.

```php
use ArtisanPack\Accessibility\PaletteGeneration\PaletteGenerator;

$generator = new PaletteGenerator();
$palette = $generator->generatePalette('#0000ff');
```

The `$palette` variable will contain an array with the primary and secondary color palettes.

## Generating Semantic Palettes

You can also generate semantic color palettes for success, warning, error, and info states.

```php
$semanticPalettes = $generator->generateSemanticPalettes('#0000ff');
```

## Color Harmonies

The `ColorHarmony` class provides methods to generate different color harmonies.

- `complementary(string $hexColor): string`
- `triadic(string $hexColor): array`
- `analogous(string $hexColor): array`
- `splitComplementary(string $hexColor): array`

```php
use ArtisanPack\Accessibility\PaletteGeneration\ColorHarmony;

$complementary = ColorHarmony::complementary('#ff0000');
```

## Exporting Palettes

You can export the generated palettes to different formats using the `export` method on the `PaletteGenerator` class. The following exporters are available:

- `CssExporter`
- `ScssExporter`
- `JsonExporter`
- `TailwindExporter`

```php
use ArtisanPack\Accessibility\PaletteGeneration\ExportFormats\JsonExporter;

$palette = $generator->generatePalette('#0000ff');
$exporter = new JsonExporter();
$json = $generator->export($exporter, $palette);
```
