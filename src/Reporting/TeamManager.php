<?php
/**
 * Manages team membership and invitations for organizations.
 *
 * @package ArtisanPack\Accessibility
 * @since 2.0.0
 */

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\Organization;
use ArtisanPack\Accessibility\Models\Team;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Team manager service.
 *
 * @since 2.0.0
 */
class TeamManager
{
	/**
	 * Invite a user to an organization with a specific role.
	 *
	 * @since 2.0.0
	 *
	 * @param Organization   $organization The organization to invite the user to.
	 * @param Authenticatable $user        The user being invited.
	 * @param string          $role        The role to assign within the team.
	 * @return Team Created team membership record.
	 */
	public function inviteUser( Organization $organization, Authenticatable $user, string $role ): Team
	{
		return $organization->teams()->create( [
										   'user_id' => $user->id,
										   'role'    => $role,
								   ] );
	}

	/**
	 * Remove a user from a team.
	 *
	 * @since 2.0.0
	 *
	 * @param Team $team Team membership to remove.
	 * @return void
	 */
	public function removeUser( Team $team ): void
	{
		$team->delete();
	}

	/**
	 * List users for an organization including user relationship.
	 *
	 * @since 2.0.0
	 *
	 * @param Organization $organization The organization to list users for.
	 * @return Collection Collection of Team models with loaded user relation.
	 */
	public function listUsers( Organization $organization ): Collection
	{
		return $organization->teams()->with( 'user' )->get();
	}
}
