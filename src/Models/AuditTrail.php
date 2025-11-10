<?php

namespace ArtisanPack\Accessibility\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
	// Allow mass assignment of user_id so audit logs can be properly attributed
	protected $guarded = [ 'id', 'created_at', 'updated_at' ];

	protected $casts = [
		'details' => 'array',
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo( config( 'auth.providers.users.model' ) );
	}
}
