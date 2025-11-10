<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Reporting;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Models\Team;
use ArtisanPack\Accessibility\Reporting\AccessManager;
use Tests\TestCase;
use Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_checks_permissions()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        Team::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'editor',
        ]);

        $accessManager = new AccessManager();

        $this->assertTrue($accessManager->can($user, 'view_reports', $organization));
        $this->assertTrue($accessManager->can($user, 'manage_reports', $organization));
        $this->assertFalse($accessManager->can($user, 'manage_teams', $organization));
    }
}
