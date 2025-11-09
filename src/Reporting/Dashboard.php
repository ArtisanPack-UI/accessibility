<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\ComplianceReport;
use Illuminate\Support\Facades\DB;

class Dashboard
{
    public function getData(int $organizationId): array
    {
        $reports = ComplianceReport::where('organization_id', $organizationId)->get();

        $totalReports = $reports->count();
        $averageScore = $reports->avg('score');

        return [
            'total_reports' => $totalReports,
            'average_score' => $averageScore,
        ];
    }
}
