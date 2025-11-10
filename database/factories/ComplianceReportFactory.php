<?php

namespace ArtisanPack\Accessibility\Database\Factories;

use ArtisanPack\Accessibility\Models\ComplianceReport;
use ArtisanPack\Accessibility\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplianceReportFactory extends Factory
{
    protected $model = ComplianceReport::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'score' => $this->faker->numberBetween(0, 100),
            'issues' => [],
        ];
    }
}
