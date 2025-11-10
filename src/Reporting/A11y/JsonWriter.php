<?php

namespace ArtisanPack\Accessibility\Reporting\A11y;

use RuntimeException;

class JsonWriter implements ReportWriterInterface
{
	public function write( AuditReport $report, string $outputPath ): string
	{
		$dir = dirname( $outputPath );
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0777, true );
		}
		$payload = json_encode( $report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		if ( $payload === false ) {
			throw new RuntimeException( 'Failed to encode report as JSON: ' . json_last_error_msg() );
		}
		if ( file_put_contents( $outputPath, $payload ) === false ) {
			throw new RuntimeException( "Failed to write report to {$outputPath}" );
		}

		return $outputPath;
	}
}
