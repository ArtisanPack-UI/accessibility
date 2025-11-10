<?php

namespace ArtisanPack\Accessibility\Console;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\WcagValidator;
use ArtisanPack\Accessibility\PaletteGeneration\PaletteGenerator;
use Illuminate\Console\Command;

class GeneratePaletteCommand extends Command
{
	protected $signature = 'a11y:generate-palette
        {--primary= : Primary color (hex)}
        {--secondary= : Secondary color (hex)}
        {--accent=* : Additional accent colors}
        {--background= : Background color to test}
        {--foreground= : Foreground color to test}
        {--strictness=AA : A|AA|AAA}
        {--size=9 : Number of tints/shades per palette}
        {--format=md : Output format json|md|html}
        {--output= : Output file path (writes to file). If not set, prints to stdout}
        {--json : Shortcut for --format=json to stdout}
    ';

	protected $description = 'Generate an accessible color palette from seed colors.';

	public function handle(): int
	{
		$primary    = $this->option( 'primary' );
		$secondary  = $this->option( 'secondary' );
		$size       = (int) ( $this->option( 'size' ) ?: 9 );
		$format     = $this->option( 'json' ) ? 'json' : strtolower( (string) ( $this->option( 'format' ) ?: 'md' ) );
		$output     = $this->option( 'output' );
		$strictness = strtoupper( $this->option( 'strictness' ) ?: 'AA' );

		if ( ! $primary ) {
			$this->error( 'You must provide --primary color (hex, e.g., #3366FF).' );
			return 1;
		}

		$generator           = new PaletteGenerator();
		$palette             = $generator->generatePalette( $primary, $size );
		$palette['semantic'] = $generator->generateSemanticPalettes( $primary, $size );

		// Optionally evaluate a foreground/background pair
		$fg    = $this->option( 'foreground' );
		$bg    = $this->option( 'background' );
		$extra = [];
		if ( $fg && $bg ) {
			$validator     = new WcagValidator();
			$ratio         = $validator->calculateContrastRatio( $fg, $bg );
			$extra['pair'] = [
				'foreground' => $fg,
				'background' => $bg,
				'ratio'      => $ratio,
			];
		}

		$content = '';
		switch ( $format ) {
			case 'json':
				$payload = [ 'palette' => $palette ] + $extra;
				$content = json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
				break;
			case 'html':
				$content = $this->renderHtml( $palette );
				break;
			case 'md':
			case 'markdown':
			default:
				$content = $this->renderMarkdown( $palette );
				break;
		}

		if ( $output ) {
			$dir = dirname( $output );
			if ( ! is_dir( $dir ) && ! mkdir( $dir, 0777, true ) && ! is_dir( $dir ) ) {
				$this->error( "Failed to create directory: {$dir}" );
				return 1;
			}

			file_put_contents( $output, $content );
			$this->line( "Wrote palette: {$output}" );
		} else {
			$this->line( $content );
		}

		return 0;
	}

	protected function renderHtml( array $palette ): string
	{
		$section = function ( string $title, array $scale ) {
			$items = '';
			foreach ( $scale as $hex ) {
				$items .= '<div class="chip"><span class="box" style="background:' . htmlspecialchars( $hex ) . '"></span><code>' . htmlspecialchars( $hex ) . '</code></div>';
			}
			return '<h2>' . htmlspecialchars( $title ) . '</h2><div class="row">' . $items . '</div>';
		};
		$html    = '<!doctype html><html><head><meta charset="utf-8"><title>Generated Palette</title><style>body{font-family:system-ui,Arial;padding:16px} .row{display:flex;flex-wrap:wrap;gap:8px} .chip{border:1px solid #ddd;padding:8px;border-radius:6px} .box{display:inline-block;width:20px;height:20px;border:1px solid #ccc;vertical-align:middle;margin-right:8px}</style></head><body>';
		$html    .= $section( 'Primary', $palette['primary'] );
		$html    .= $section( 'Secondary', $palette['secondary'] );
		foreach ( $palette['semantic'] as $name => $scale ) {
			$html .= $section( ucfirst( $name ), $scale );
		}
		$html .= '</body></html>';
		return $html;
	}

	protected function renderMarkdown( array $palette ): string
	{
		$lines = [
			'# Generated Palette',
			'',
			'## Primary',
			$this->swatchList( $palette['primary'] ),
			'',
			'## Secondary',
			$this->swatchList( $palette['secondary'] ),
			'',
			'## Semantic',
		];
		foreach ( $palette['semantic'] as $name => $scale ) {
			$lines[] = "### " . ucfirst( $name );
			$lines[] = $this->swatchList( $scale );
			$lines[] = '';
		}
		return implode( "\n", $lines ) . "\n";
	}

	protected function swatchList( array $scale ): string
	{
		$parts = array_map( fn( $hex ) => sprintf( '`%s`', $hex ), $scale );
		return implode( ' ', $parts );
	}
}
