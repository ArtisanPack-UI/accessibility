<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Core\Analysis\AccessibilityScorer;
use ArtisanPack\Accessibility\Models\ComplianceReport;
use ArtisanPack\Accessibility\Core\WcagValidator;

class ComplianceReporter
{
    protected AccessibilityScorer $scorer;

    public function __construct(AccessibilityScorer $scorer)
    {
        $this->scorer = $scorer;
    }

    public function generate(string $foregroundColor, string $backgroundColor, int $organizationId): ComplianceReport
    {
        $score = $this->scorer->calculateScore($foregroundColor, $backgroundColor);
        $recommendations = $this->scorer->getRecommendations($foregroundColor, $backgroundColor);

        $report = ComplianceReport::create([
            'organization_id' => $organizationId,
            'score' => $score,
            'issues' => $recommendations,
        ]);

        return $report;
    }
}
