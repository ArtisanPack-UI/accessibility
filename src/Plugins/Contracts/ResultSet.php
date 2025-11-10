<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

use JsonSerializable;

final class ResultSet implements JsonSerializable
{
	/** @var array<int, array{rule:string, level:string, message:string, data?:array}> */
	private array $items = [];

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
	 * @return array<int, array{rule:string, level:string, message:string, data?:array}>
	 */
	public function all(): array
	{
		return $this->items;
	}

	public function jsonSerialize(): array
	{
		return $this->items;
	}
}
