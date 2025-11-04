<?php

namespace ArtisanPack\Accessibility\Core\Theming;

class CssVariableParser
{
    public function parse(string $cssValue): ?string
    {
        if (str_starts_with($cssValue, 'var(') && str_ends_with($cssValue, ')')) {
            return trim(substr($cssValue, 4, -1));
        }

        return null;
    }

    public function resolve(string $variableName, array $theme): ?string
    {
        return $theme[$variableName] ?? null;
    }
}
