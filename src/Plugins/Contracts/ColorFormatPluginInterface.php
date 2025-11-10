<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

interface ColorFormatPluginInterface
{
    /**
     * @return string[] list of supported format names (e.g., ["hex", "rgb"]).
     */
    public function getSupportedFormats(): array;

    /**
     * Parse input string to a normalized hex string (e.g., #RRGGBB).
     */
    public function parse(string $input): string;

    /**
     * Serialize normalized hex string into a target format name.
     */
    public function serialize(string $hex, string $format): string;
}
