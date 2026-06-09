<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\Organization;
use Illuminate\Contracts\Auth\Authenticatable;

class AccessManager
{
    public function can(Authenticatable $user, string $permission, Organization $organization): bool
    {
        $team = $organization->teams()->where('user_id', $user->id)->first();

        if (! $team) {
            return false;
        }

        $role = $team->role;

        $permissions = config('artisanpack.accessibility.roles.'.$role);

        if (! $permissions) {
            return false;
        }

        return in_array($permission, $permissions);
    }
}
