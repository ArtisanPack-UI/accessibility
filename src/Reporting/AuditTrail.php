<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\AuditTrail as AuditTrailModel;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class AuditTrail
{
	public function log( string $action, array $details = [] ): void
	{
		$userId = Auth::id();

		if ( $userId === null ) {
			throw new RuntimeException( 'Cannot log audit trail: no authenticated user' );
		}

		AuditTrailModel::create( [
									 'user_id' => $userId,
									 'action'  => $action,
									 'details' => $details,
								 ] );
	}
}
