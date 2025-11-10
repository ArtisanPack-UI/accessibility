<?php

namespace ArtisanPack\Accessibility\Core\Theming;

use ArtisanPack\Accessibility\Core\WcagValidator;

class ThemeValidator
{
    public function __construct(protected WcagValidator $wcagValidator)
    {
    }

    public function validate(array $theme): bool
    {
        foreach ($theme as $mode => $colors) {
            foreach ($colors as $variableName => $color) {
                // Assuming the background is either black or white based on the mode
                $background = ($mode === 'light') ? '#ffffff' : '#000000';
                if (!$this->wcagValidator->checkContrast($color, $background)) {
                    return false;
                }
            }
        }

        return true;
    }
}
