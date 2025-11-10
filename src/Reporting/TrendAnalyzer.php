<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\ComplianceReport;
use Illuminate\Support\Carbon;

class TrendAnalyzer
{
    public function analyze(int $organizationId, int $days = 30): array
    {
        $reports = ComplianceReport::where('organization_id', $organizationId)
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at')
            ->get(['score', 'created_at']);

        return $reports->map(function ($report) {
            return [
                'score' => $report->score,
                'date' => $report->created_at->toDateString(),
            ];
        })->toArray();
    }
}
