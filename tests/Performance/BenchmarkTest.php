<?php

namespace Tests\Performance;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\WcagValidator;

class BenchmarkTest
{
    private $wcagValidator;
    private $colorGenerator;
    private $colors = [
        '#ff0000',
        '#00ff00',
        '#0000ff',
        '#ffff00',
        '#00ffff',
        '#ff00ff',
        '#ffffff',
        '#000000',
        '#f87171',
        '#3b82f6',
    ];

    public function __construct()
    {
        $this->wcagValidator = new WcagValidator();
        $this->colorGenerator = new AccessibleColorGenerator($this->wcagValidator);
    }

    /**
     * @Revs(100)
     * @Iterations(5)
     */
    public function benchCalculateContrastRatio()
    {
        $this->wcagValidator->calculateContrastRatio('#ff0000', '#000000');
    }

    /**
     * @Revs(100)
     * @Iterations(5)
     */
    public function benchFindClosestAccessibleShade()
    {
        $this->colorGenerator->generateAccessibleTextColor('#ff0000', true);
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchBulkColorProcessing()
    {
        foreach ($this->colors as $color) {
            $this->colorGenerator->generateAccessibleTextColor($color, false);
        }
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchBulkColorProcessingWithTint()
    {
        foreach ($this->colors as $color) {
            $this->colorGenerator->generateAccessibleTextColor($color, true);
        }
    }
}
