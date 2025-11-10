<?php

namespace ArtisanPack\Accessibility\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplianceReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'issues' => 'array',
    ];

    protected static function newFactory()
    {
        return \ArtisanPack\Accessibility\Database\Factories\ComplianceReportFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
