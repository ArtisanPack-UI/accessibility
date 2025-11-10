<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Reporting;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Models\Team;
use ArtisanPack\Accessibility\Reporting\TeamManager;
use Tests\TestCase;
use Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_invites_a_user()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();

        $teamManager = new TeamManager();
        $teamManager->inviteUser($organization, $user, 'editor');

        $this->assertDatabaseHas('teams', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'editor',
        ]);
    }

    /** @test */
    public function it_removes_a_user()
    {
        $team = Team::factory()->create();

        $teamManager = new TeamManager();
        $teamManager->removeUser($team);

        $this->assertDatabaseMissing('teams', [
            'id' => $team->id,
        ]);
    }

    /** @test */
    public function it_lists_users()
    {
        $organization = Organization::factory()->create();
        Team::factory()->count(5)->create(['organization_id' => $organization->id]);

        $teamManager = new TeamManager();
        $users = $teamManager->listUsers($organization);

        $this->assertCount(5, $users);
    }
}
