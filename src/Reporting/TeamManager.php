<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Models\Team;
use Tests\User;

class TeamManager
{
    public function inviteUser(Organization $organization, User $user, string $role): Team
    {
        return $organization->teams()->create([
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }

    public function removeUser(Team $team): void
    {
        $team->delete();
    }

    public function listUsers(Organization $organization): \Illuminate\Database\Eloquent\Collection
    {
        return $organization->teams()->with('user')->get();
    }
}
