<?php

namespace ArtisanPack\Accessibility\Database\Factories;

use ArtisanPack\Accessibility\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
        ];
    }
}
