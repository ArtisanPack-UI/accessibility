<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Reporting;

use ArtisanPack\Accessibility\Models\ComplianceReport;
use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Reporting\Dashboard;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_dashboard_data()
    {
        $organization = Organization::factory()->create();
        ComplianceReport::factory()->count(5)->create(['organization_id' => $organization->id, 'score' => 80]);
        ComplianceReport::factory()->count(5)->create(['organization_id' => $organization->id, 'score' => 90]);

        $dashboard = new Dashboard();
        $data = $dashboard->getData($organization->id);

        $this->assertEquals(10, $data['total_reports']);
        $this->assertEquals(85, $data['average_score']);
    }
}
