<?php

namespace ArtisanPack\Accessibility\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ColorContrastChecked
{
    use Dispatchable, SerializesModels;

    public string $color1;
    public string $color2;
    public string $level;
    public bool $isLargeText;
    public bool $result;

    public function __construct(string $color1, string $color2, string $level, bool $isLargeText, bool $result)
    {
        $this->color1 = $color1;
        $this->color2 = $color2;
        $this->level = $level;
        $this->isLargeText = $isLargeText;
        $this->result = $result;
    }
}
