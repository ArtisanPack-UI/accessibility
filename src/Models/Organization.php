<?php

namespace ArtisanPack\Accessibility\Models;

use ArtisanPack\Accessibility\Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
	];


	protected static function newFactory()
	{
		return OrganizationFactory::new();
	}

	public function reports(): HasMany
	{
		return $this->hasMany( ComplianceReport::class );
	}

	public function teams(): HasMany
	{
		return $this->hasMany( Team::class );
	}
}
