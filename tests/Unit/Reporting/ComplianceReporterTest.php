<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Reporting;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Reporting\ComplianceReporter;
use ArtisanPack\Accessibility\Core\Analysis\AccessibilityScorer;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplianceReporterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_a_compliance_report()
    {
        $organization = Organization::factory()->create();
        $scorer = $this->mock(AccessibilityScorer::class);

        $scorer->shouldReceive('calculateScore')->with('#ffffff', '#000000')->andReturn(100);
        $scorer->shouldReceive('getRecommendations')->with('#ffffff', '#000000')->andReturn([]);

        $reporter = new ComplianceReporter($scorer);
        $report = $reporter->generate('#ffffff', '#000000', $organization->id);

        $this->assertEquals($organization->id, $report->organization_id);
        $this->assertEquals(100, $report->score);
        $this->assertEquals([], $report->issues);
    }
}
