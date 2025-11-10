<?php

namespace Plugins\Examples\ColorFormatHexPlugin;

use ArtisanPack\Accessibility\Plugins\Contracts\Capability;
use ArtisanPack\Accessibility\Plugins\Contracts\ColorFormatPluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata;

class HexColorFormatPlugin implements PluginInterface, ColorFormatPluginInterface
{
    private ?Context $context = null;

    public function getMetadata(): PluginMetadata
    {
        return new PluginMetadata(
            id: 'example.hex',
            name: 'Hex Color Format Plugin',
            version: '0.1.0',
            description: 'Provides hex color format parsing/serialization.',
            author: 'Example',
            capabilities: [Capability::COLOR_FORMAT]
        );
    }

    public function initialize(Context $context): void
    {
        $this->context = $context;
    }

    public function start(): void {}

    public function stop(): void {}

    public function destroy(): void {}

    public function getSupportedFormats(): array
    {
        return ['hex'];
    }

    public function parse(string $input): string
    {
        $hex = strtolower(trim($input));
        if ($hex[0] !== '#') {
            $hex = '#' . $hex;
        }
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (!preg_match('/^[0-9a-f]{6}$/', $hex)) {
            throw new \InvalidArgumentException('Invalid hex color: ' . $input);
        }
        return '#' . $hex;
    }

    public function serialize(string $hex, string $format): string
    {
        if ($format !== 'hex') {
            throw new \InvalidArgumentException('Unsupported format: ' . $format);
        }
        return $this->parse($hex);
    }
}
