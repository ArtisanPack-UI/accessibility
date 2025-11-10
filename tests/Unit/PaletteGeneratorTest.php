<?php

namespace Tests\Unit;

use ArtisanPack\Accessibility\PaletteGeneration\PaletteGenerator;
use ArtisanPack\Accessibility\PaletteGeneration\ExportFormats\JsonExporter;
use ArtisanPack\Accessibility\Core\WcagValidator;
use PHPUnit\Framework\TestCase;

class PaletteGeneratorTest extends TestCase
{
    private $paletteGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $wcagValidatorMock = $this->createMock(WcagValidator::class);
        $this->paletteGenerator = new PaletteGenerator($wcagValidatorMock);
    }

    public function test_generate_palette()
    {
        $palette = $this->paletteGenerator->generatePalette('#0000ff');

        $this->assertArrayHasKey('primary', $palette);
        $this->assertArrayHasKey('secondary', $palette);
        $this->assertCount(9, $palette['primary']);
        $this->assertCount(9, $palette['secondary']);
    }

    public function test_generate_semantic_palettes()
    {
        $semanticPalettes = $this->paletteGenerator->generateSemanticPalettes('#0000ff');

        $this->assertArrayHasKey('success', $semanticPalettes);
        $this->assertArrayHasKey('warning', $semanticPalettes);
        $this->assertArrayHasKey('error', $semanticPalettes);
        $this->assertArrayHasKey('info', $semanticPalettes);
        $this->assertCount(9, $semanticPalettes['success']);
    }

    public function test_generate_color_scale()
    {
        $scale = $this->paletteGenerator->generateColorScale('#0000ff', 5);
        $this->assertCount(5, $scale);
    }

    public function test_export()
    {
        $palette = $this->paletteGenerator->generatePalette('#0000ff');
        $exporter = new JsonExporter();
        $json = $this->paletteGenerator->export($exporter, $palette);

        $this->assertJson($json);
    }
}