<?php

namespace ArtisanPack\Accessibility\Database\Factories;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\User;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'role' => 'viewer',
        ];
    }
}
