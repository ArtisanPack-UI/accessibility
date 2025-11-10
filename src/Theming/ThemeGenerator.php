<?php

namespace ArtisanPack\Accessibility\Core\Theming;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\WcagValidator;

class ThemeGenerator
{
	public function __construct(
		protected AccessibleColorGenerator $colorGenerator,
		protected CssVariableParser        $parser,
		protected ?ThemeValidator          $validator = null
	)
	{
	}

	public function generate( array $baseColors ): array
	{
		$lightTheme = [];
		$darkTheme  = [];

		foreach ( $baseColors as $name => $color ) {
			$variableName                = '--color-' . $name;
			$lightTheme[ $variableName ] = $this->generateFor( $color, 'light' );
			$darkTheme[ $variableName ]  = $this->generateFor( $color, 'dark' );
		}

		$theme = [
			'light' => $lightTheme,
			'dark'  => $darkTheme,
		];

		if ( $this->validator && ! $this->validator->validate( $theme ) ) {
			// Or throw an exception, depending on desired behavior
			return [];
		}

		return $theme;
	}

	public function generateFor( string $color, string $mode = 'light' ): string
	{
		$background = $mode === 'light' ? '#ffffff' : '#000000';
		$hex        = $this->colorGenerator->getHexFromColorString( $color ) ?? '#000000';
		$validator  = new WcagValidator();
		if ( $validator->checkContrast( $hex, $background, 'AA', false ) ) {
			return $hex;
		}

		return $this->colorGenerator->generateAccessibleTextColor( $background, false, 'AA', false );
	}

	public function export( array $theme, string $format = 'css' ): string
	{
		return match ( $format ) {
			'css' => $this->exportToCss( $theme ),
			'json' => json_encode( $theme, JSON_PRETTY_PRINT ),
			default => '',
		};
	}

	protected function exportToCss( array $theme ): string
	{
		$css = ":root {\n";
		foreach ( $theme['light'] as $key => $value ) {
			$css .= "    {$key}: {$value};\n";
		}
		$css .= "}\n";

		$css .= "@media (prefers-color-scheme: dark) {\n";
		$css .= "    :root {\n";
		foreach ( $theme['dark'] as $key => $value ) {
			$css .= "        {$key}: {$value};\n";
		}
		$css .= "    }\n";
		$css .= "}\n";

		return $css;
	}
}
