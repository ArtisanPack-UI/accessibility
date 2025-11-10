<?php

namespace ArtisanPack\Accessibility\Database\Factories;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Models\Team;
use Tests\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
