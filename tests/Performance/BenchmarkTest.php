<?php

namespace Tests\Performance;

use ArtisanPackUI\Accessibility\A11y;
use ArtisanPackUI\Accessibility\AccessibleColorGenerator;

class BenchmarkTest
{
    private $a11y;
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
        $this->a11y = new A11y();
        $this->colorGenerator = new AccessibleColorGenerator();
    }

    /**
     * @Revs(100)
     * @Iterations(5)
     */
    public function benchCalculateContrastRatio()
    {
        $this->a11y->calculateContrastRatio('#ff0000', '#000000');
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
