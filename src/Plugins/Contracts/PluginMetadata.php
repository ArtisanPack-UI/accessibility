<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

final class PluginMetadata
{
    public function __construct(
        public string $id,
        public string $name,
        public string $version,
        public ?string $description = null,
        public ?string $author = null,
        public ?string $license = null,
        public ?string $homepage = null,
        /** @var Capability[] */
        public array $capabilities = [],
        public array $requires = [],
        public ?string $compatibleWith = null,
        public ?array $permissions = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        $required = ['id', 'name', 'version', 'capabilities'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException("Missing required metadata field: {$key}");
            }
        }

        $caps = [];
        foreach ($data['capabilities'] as $cap) {
            $caps[] = is_string($cap) ? Capability::from($cap) : $cap;
        }

        return new self(
            id: (string) $data['id'],
            name: (string) $data['name'],
            version: (string) $data['version'],
            description: $data['description'] ?? null,
            author: $data['author'] ?? null,
            license: $data['license'] ?? null,
            homepage: $data['homepage'] ?? null,
            capabilities: $caps,
            requires: $data['requires'] ?? [],
            compatibleWith: $data['compatibleWith'] ?? null,
            permissions: $data['permissions'] ?? null,
        );
    }
}
