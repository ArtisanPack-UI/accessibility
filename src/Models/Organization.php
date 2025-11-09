<?php

namespace ArtisanPack\Accessibility\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $guarded = [];

    public function reports(): HasMany
    {
        return $this->hasMany(ComplianceReport::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
