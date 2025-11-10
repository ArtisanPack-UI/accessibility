<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Reporting;

use ArtisanPack\Accessibility\Models\ComplianceReport;
use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Reporting\TrendAnalyzer;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class TrendAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_analyzes_trends()
    {
        $organization = Organization::factory()->create();

        ComplianceReport::factory()->create([
            'organization_id' => $organization->id,
            'score' => 80,
            'created_at' => Carbon::now()->subDays(20),
        ]);

        ComplianceReport::factory()->create([
            'organization_id' => $organization->id,
            'score' => 90,
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $analyzer = new TrendAnalyzer();
        $data = $analyzer->analyze($organization->id);

        $this->assertCount(2, $data);
        $this->assertEquals(80, $data[0]['score']);
        $this->assertEquals(90, $data[1]['score']);
    }
}
