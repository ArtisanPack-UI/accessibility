<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

final class Report implements \JsonSerializable
{
    public function __construct(
        public string $title,
        /** @var array<string,mixed> */
        public array $data = []
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'data' => $this->data,
        ];
    }
}
