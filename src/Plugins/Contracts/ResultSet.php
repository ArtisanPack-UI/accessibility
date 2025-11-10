<?php
/**
 * Collection of accessibility rule results.
 *
 * Provides a lightweight container to collect rule messages and serialize them
 * to JSON for reporting.
 *
 * @package ArtisanPack\Accessibility
 * @since 2.0.0
 */

namespace ArtisanPack\Accessibility\Plugins\Contracts;

use JsonSerializable;

/**
 * Represents a set of results produced by accessibility checks.
 *
 * @since 2.0.0
 */
final class ResultSet implements JsonSerializable
{
	/**
	 * Stored result items.
	 *
	 * @since 2.0.0
	 * @var array<int, array{rule:string, level:string, message:string, data?:array}>
	 */
	private array $items = [];

	/**
	 * Add a result to the set.
	 *
	 * @since 2.0.0
	 *
	 * @param string $rule    The rule identifier that produced the result.
	 * @param string $level   The severity level (e.g. error, warning, info).
	 * @param string $message Human-readable message describing the result.
	 * @param array  $data    Optional contextual data for the result.
	 * @return void
	 */
	public function add( string $rule, string $level, string $message, array $data = [] ): void
	{
		$item = [
			'rule'    => $rule,
			'level'   => $level,
			'message' => $message,
		];

		if ( $data !== [] ) {
			$item['data'] = $data;
		}
		$this->items[] = $item;
	}

	/**
	 * Get all results in the set.
	 *
	 * @since 2.0.0
	 *
	 * @return array<int, array{rule:string, level:string, message:string, data?:array}> Array of result items.
	 */
	public function all(): array
	{
		return $this->items;
	}

	/**
	 * Specify data which should be serialized to JSON.
	 *
	 * @since 2.0.0
	 *
	 * @return array<int, array{rule:string, level:string, message:string, data?:array}> Serializable array of result items.
	 */
	public function jsonSerialize(): array
	{
		return $this->items;
	}
}
