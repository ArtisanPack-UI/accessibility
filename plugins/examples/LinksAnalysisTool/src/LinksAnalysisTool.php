<?php
/**
 * Example Analysis Tool plugin that inspects links in a subject.
 *
 * Counts total links and those missing text to demonstrate analysis tooling.
 *
 * @since   2.0.0
 * @package ArtisanPack\Accessibility
 */

namespace Plugins\Examples\LinksAnalysisTool;

use ArtisanPack\Accessibility\Plugins\Contracts\AnalysisToolPluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Capability;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata;
use ArtisanPack\Accessibility\Plugins\Contracts\Report;

/**
 * Links Analysis Tool example plugin.
 *
 * @since 2.0.0
 */
class LinksAnalysisTool implements PluginInterface, AnalysisToolPluginInterface
{
	/**
	 * Runtime plugin context.
	 *
	 * @since 2.0.0
	 * @var Context|null
	 */
	private ?Context $context = null;

	/**
	 * Get plugin metadata.
	 *
	 * @since 2.0.0
	 *
	 * @return PluginMetadata Plugin metadata.
	 */
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

	/**
	 * Initialize the plugin with a context.
	 *
	 * @since 2.0.0
	 *
	 * @param Context $context Execution context provided by the host.
	 * @return void
	 */
	public function initialize( Context $context ): void
	{
		$this->context = $context;
	}

	/**
	 * Start the plugin lifecycle.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function start(): void
	{
	}

	/**
	 * Stop the plugin lifecycle.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function stop(): void
	{
	}

	/**
	 * Destroy the plugin, releasing resources.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function destroy(): void
	{
	}

	/**
	 * Analyze the given subject for link metrics.
	 *
	 * @since 2.0.0
	 *
	 * @param array   $subject Arbitrary subject data; expects a 'links' array.
	 * @param Context $context Execution context for analysis.
	 * @return Report Analysis report containing counts.
	 */
	public function analyze( array $subject, Context $context ): Report
	{
		$links = $subject['links'] ?? [];
		if ( ! is_array( $links ) ) {
			$links = [];
		}
		$total       = count( $links );
		$missingText = 0;
		foreach ( $links as $link ) {
			$text = '';
			if ( is_array( $link ) ) {
				$text = $link['text'] ?? '';
			} else if ( is_object( $link ) ) {
				$text = $link->text ?? '';
			}

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
