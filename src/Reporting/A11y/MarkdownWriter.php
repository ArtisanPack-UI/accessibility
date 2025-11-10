<?php

namespace ArtisanPack\Accessibility\Reporting\A11y;

class MarkdownWriter implements ReportWriterInterface
{
	public function write( AuditReport $report, string $outputPath ): string
	{
		$dir = dirname( $outputPath );
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true );
		}
		$data    = $report->toArray();
		$m       = $data['metadata'];
		$tot     = $m['totals'];
		$lines   = [];
		$lines[] = '# Accessibility Color Audit';
		$lines[] = '';
		$lines[] = sprintf( '- Strictness: %s', $m['strictness'] );
		$lines[] = sprintf( '- Started: %s', $m['startedAt'] );
		$lines[] = sprintf( '- Duration: %d ms', (int) $m['durationMs'] );
		$lines[] = sprintf( '- Files: %d | Checks: %d | Passes: %d | Violations: %d | Warnings: %d',
							(int) $tot['files'], (int) $tot['checks'], (int) $tot['passes'], (int) $tot['violations'], (int) $tot['warnings'] );
		$lines[] = '';
		$lines[] = '| File | Foreground | Background | Ratio | Severity | Suggestion |';
		$lines[] = '|------|------------|------------|-------|----------|-----------|';
		foreach ( $data['findings'] as $f ) {
			$lines[] = sprintf( '| %s | `%s` | `%s` | %.2f | %s | %s |',
								$f['file'], $f['foreground'], $f['background'], $f['ratio'], $f['severity'], $f['recommendation']['suggestedForeground'] ?? '' );
		}
		$md = implode( "\n", $lines ) . "\n";
		file_put_contents( $outputPath, $md );
		return $outputPath;
	}
}
