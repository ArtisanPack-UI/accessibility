<?php

namespace ArtisanPackUI\Accessibility\Analysis;

use ArtisanPackUI\Accessibility\WcagValidator;

class AccessibilityScorer
{
    private WcagValidator $wcagValidator;

    public function __construct(WcagValidator $wcagValidator = null)
    {
        $this->wcagValidator = $wcagValidator ?? new WcagValidator();
    }

    public function calculateScore(string $foregroundColor, string $backgroundColor): int
    {
        $contrastRatio = $this->wcagValidator->calculateContrastRatio($foregroundColor, $backgroundColor);

        $score = ($contrastRatio - 1) / 20 * 100;

        return (int) round($score);
    }

    public function getRecommendations(string $foregroundColor, string $backgroundColor): array
    {
        $recommendations = [];

        $aa_normal = $this->wcagValidator->checkContrast($foregroundColor, $backgroundColor, 'AA');
        $aaa_normal = $this->wcagValidator->checkContrast($foregroundColor, $backgroundColor, 'AAA');
        $aa_large = $this->wcagValidator->checkContrast($foregroundColor, $backgroundColor, 'AA', true);
        $aaa_large = $this->wcagValidator->checkContrast($foregroundColor, $backgroundColor, 'AAA', true);

        if ($aa_normal && $aaa_normal && $aa_large && $aaa_large) {
            return ['message' => 'All WCAG standards are met.'];
        }

        if (!$aa_normal) {
            $recommendations['AA_normal'] = $this->getSuggestion($foregroundColor, $backgroundColor, 'AA');
        }

        if (!$aaa_normal) {
            $recommendations['AAA_normal'] = $this->getSuggestion($foregroundColor, $backgroundColor, 'AAA');
        }

        if (!$aa_large) {
            $recommendations['AA_large'] = $this->getSuggestion($foregroundColor, $backgroundColor, 'AA', true);
        }

        if (!$aaa_large) {
            $recommendations['AAA_large'] = $this->getSuggestion($foregroundColor, $backgroundColor, 'AAA', true);
        }

        return $recommendations;
    }

    private function getSuggestion(string $foregroundColor, string $backgroundColor, string $level, bool $isLargeText = false): array
    {
        $generator = new \ArtisanPackUI\Accessibility\AccessibleColorGenerator($this->wcagValidator);

        $suggestedForeground = $generator->generateAccessibleTextColor($backgroundColor, true, $level, $isLargeText);
        $suggestedBackground = $generator->generateAccessibleTextColor($foregroundColor, true, $level, $isLargeText);

        return [
            'suggested_foreground' => $suggestedForeground,
            'suggested_background' => $suggestedBackground,
        ];
    }
}
