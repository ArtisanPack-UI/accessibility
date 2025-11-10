<?php

namespace Plugins\Examples\LinksAnalysisTool;

use ArtisanPack\Accessibility\Plugins\Contracts\AnalysisToolPluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Capability;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata;
use ArtisanPack\Accessibility\Plugins\Contracts\Report;

class LinksAnalysisTool implements PluginInterface, AnalysisToolPluginInterface
{
	private ?Context $context = null;

	public function getMetadata(): PluginMetadata
	{
		return new PluginMetadata(
			id:           'example.links_analysis',
			name:         'Links Analysis Tool',
			version:      '0.1.0',
			description:  "Analyzes a document's links for counts and potential issues.",
			author:       'Example',
			capabilities: [ Capability::ANALYSIS_TOOL ]
		);
	}

	public function initialize( Context $context ): void
	{
		$this->context = $context;
	}

	public function start(): void
	{
	}

	public function stop(): void
	{
	}

	public function destroy(): void
	{
	}

	public function analyze( array $subject, Context $context ): Report
	{
		$links = $subject['links'] ?? [];
		if ( ! is_array( $links ) ) {
			$links = [];
		}
		$total       = count( $links );
		$missingText = 0;
		foreach ( $links as $link ) {
			$text = $link['text'] ?? '';
			if ( trim( (string) $text ) === '' ) {
				$missingText++;
			}
		}
		return new Report( 'Links Analysis', [
			'total'        => $total,
			'missing_text' => $missingText,
		] );
	}
}
