<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Models\Team;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;

class TeamManager
{
	public function inviteUser( Organization $organization, Authenticatable $user, string $role ): Team
	{
		return $organization->teams()->create( [
												   'user_id' => $user->id,
												   'role'    => $role,
											   ] );
	}

	public function removeUser( Team $team ): void
	{
		$team->delete();
	}

	public function listUsers( Organization $organization ): Collection
	{
		return $organization->teams()->with( 'user' )->get();
	}
}
