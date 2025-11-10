<?php
/**
 * Example Color Format plugin that handles Hex color values.
 *
 * Demonstrates a simple implementation of a ColorFormatPluginInterface that
 * parses and serializes HEX color values.
 *
 * @package ArtisanPack\Accessibility
 * @since 2.0.0
 */

namespace Plugins\Examples\ColorFormatHexPlugin;

use ArtisanPack\Accessibility\Plugins\Contracts\Capability;
use ArtisanPack\Accessibility\Plugins\Contracts\ColorFormatPluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata;
use InvalidArgumentException;

/**
 * Hex Color Format plugin.
 *
 * @since 2.0.0
 */
class HexColorFormatPlugin implements PluginInterface, ColorFormatPluginInterface
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
	 * @return PluginMetadata Plugin metadata object.
	 */
	public function getMetadata(): PluginMetadata
	{
		return new PluginMetadata(
			id:           'example.hex',
			name:         'Hex Color Format Plugin',
			version:      '0.1.0',
			description:  'Provides hex color format parsing/serialization.',
			author:       'Example',
			capabilities: [ Capability::COLOR_FORMAT ]
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
	 * Get the supported color formats.
	 *
	 * @since 2.0.0
	 *
	 * @return string[] List of supported formats.
	 */
	public function getSupportedFormats(): array
	{
		return [ 'hex' ];
	}

	/**
	 * Serialize a HEX value into the requested format.
	 *
	 * @since 2.0.0
	 *
	 * @param string $hex    HEX value to serialize (with or without '#').
	 * @param string $format Target format (only 'hex' is supported).
	 * @return string Serialized color string.
	 *
	 * @throws InvalidArgumentException If an unsupported format is provided.
	 */
	public function serialize( string $hex, string $format ): string
	{
		if ( $format !== 'hex' ) {
			throw new InvalidArgumentException( 'Unsupported format: ' . $format );
		}
		return $this->parse( $hex );
	}

	/**
	 * Parse a color input into canonical HEX form (#rrggbb).
	 *
	 * @since 2.0.0
	 *
	 * @param string $input Input color string.
	 * @return string Canonical HEX color string.
	 *
	 * @throws InvalidArgumentException If the input is not a valid HEX color.
	 */
	public function parse( string $input ): string
	{
		$hex = strtolower( trim( $input ) );
		if ( '' === $hex ) {
			throw new InvalidArgumentException( 'Invalid hex color: ' . $input );
		}
		if ( $hex[0] !== '#' ) {
			$hex = '#' . $hex;
		}
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( ! preg_match( '/^[0-9a-f]{6}$/', $hex ) ) {
			throw new InvalidArgumentException( 'Invalid hex color: ' . $input );
		}
		return '#' . $hex;
	}
}
