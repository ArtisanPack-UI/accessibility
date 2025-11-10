<?php

namespace ArtisanPack\Accessibility\PaletteGeneration\ExportFormats;

use RuntimeException;

class JsonExporter implements Exporter
{
	public function export( array $palette ): string
	{
		$json = json_encode( $palette, JSON_PRETTY_PRINT );
		if ( $json === false ) {
			throw new RuntimeException( 'Failed to encode palette as JSON: ' . json_last_error_msg() );
		}
		return $json;
	}
}