<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\ComplianceReport;
use Illuminate\Support\Facades\DB;

class Dashboard
{
	public function getData( int $organizationId ): array
	{

		$totalReports = ComplianceReport::where( 'organization_id', $organizationId )->count();
		$averageScore = ComplianceReport::where( 'organization_id', $organizationId )->avg( 'score' );

		return [
			'total_reports' => $totalReports,
			'average_score' => $averageScore,
		];
	}
}
