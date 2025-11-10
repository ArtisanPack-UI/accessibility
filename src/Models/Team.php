<?php

namespace ArtisanPack\Accessibility\Models;

use ArtisanPack\Accessibility\Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
	use HasFactory;

	protected $fillable = [
		'organization_id',
		'user_id',
		'role',
	];


	protected static function newFactory()
	{
		return TeamFactory::new();
	}

	public function organization(): BelongsTo
	{
		return $this->belongsTo( Organization::class );
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo( config( 'auth.providers.users.model' ) );
	}
}
