<?php

namespace ArtisanPack\Accessibility\Models;

use ArtisanPack\Accessibility\Database\Factories\ComplianceReportFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplianceReport extends Model
{
	use HasFactory;

	protected $fillable = [
		'organization_id',
		'score',
		'issues',
	];


	protected $casts = [
		'issues' => 'array',
	];

	protected static function newFactory()
	{
		return ComplianceReportFactory::new();
	}

	public function organization(): BelongsTo
	{
		return $this->belongsTo( Organization::class );
	}
}
