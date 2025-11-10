<?php

namespace ArtisanPack\Accessibility\Listeners;

use ArtisanPack\Accessibility\Events\ColorContrastChecked;
use ArtisanPack\Accessibility\Reporting\AuditTrail;

class LogColorContrastCheck
{
    protected AuditTrail $auditTrail;

    public function __construct(AuditTrail $auditTrail)
    {
        $this->auditTrail = $auditTrail;
    }

    public function handle(ColorContrastChecked $event): void
    {
        $this->auditTrail->log('color_contrast_checked', [
            'color1' => $event->color1,
            'color2' => $event->color2,
            'level' => $event->level,
            'is_large_text' => $event->isLargeText,
            'result' => $event->result,
        ]);
    }
}
