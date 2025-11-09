<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AuditTrail
{
    public function log(string $action, array $details = []): void
    {
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
