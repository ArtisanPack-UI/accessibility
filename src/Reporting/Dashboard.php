<?php

/**
 * Provides aggregated dashboard data for reporting.
 *
 * @since 2.0.0
 */

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\ComplianceReport;

/**
 * Dashboard reporting service.
 *
 * @since 2.0.0
 */
class Dashboard
{
    /**
     * Get summary statistics for an organization.
     *
     * @since 2.0.0
     *
     * @param  int  $organizationId  Organization identifier.
     * @return array{total_reports:int, average_score:float|null} Summary data.
     */
    public function getData(int $organizationId): array
    {
        $totalReports = ComplianceReport::where('organization_id', $organizationId)->count();
        $averageScore = ComplianceReport::where('organization_id', $organizationId)->avg('score');

        return [
            'total_reports' => $totalReports,
            'average_score' => $averageScore,
        ];
    }
}
