<?php

namespace ArtisanPack\Accessibility\Core\Analysis;

class ReportGenerator
{
    private ColorBlindnessSimulator $colorBlindnessSimulator;
    private PerceptualAnalyzer $perceptualAnalyzer;
    private AccessibilityScorer $accessibilityScorer;

    public function __construct(
        ColorBlindnessSimulator $colorBlindnessSimulator = null,
        PerceptualAnalyzer $perceptualAnalyzer = null,
        AccessibilityScorer $accessibilityScorer = null
    ) {
        $this->colorBlindnessSimulator = $colorBlindnessSimulator ?? new ColorBlindnessSimulator();
        $this->perceptualAnalyzer = $perceptualAnalyzer ?? new PerceptualAnalyzer();
        $this->accessibilityScorer = $accessibilityScorer ?? new AccessibilityScorer();
    }

    public function generate(string $foregroundColor, string $backgroundColor): array
    {
        return [
            'color_blindness_simulation' => [
                'protanopia' => $this->colorBlindnessSimulator->simulateProtanopia($foregroundColor),
                'deuteranopia' => $this->colorBlindnessSimulator->simulateDeuteranopia($foregroundColor),
                'tritanopia' => $this->colorBlindnessSimulator->simulateTritanopia($foregroundColor),
            ],
            'perceptual_analysis' => [
                'delta_e' => $this->perceptualAnalyzer->calculateDeltaE($foregroundColor, $backgroundColor),
                'complementary' => $this->perceptualAnalyzer->getComplementaryColor($foregroundColor),
                'analogous' => $this->perceptualAnalyzer->getAnalogousColors($foregroundColor),
                'triadic' => $this->perceptualAnalyzer->getTriadicColors($foregroundColor),
            ],
            'accessibility_score' => $this->accessibilityScorer->calculateScore($foregroundColor, $backgroundColor),
            'recommendations' => $this->accessibilityScorer->getRecommendations($foregroundColor, $backgroundColor),
        ];
    }
}
