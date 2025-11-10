<?php

namespace ArtisanPack\Accessibility\Reporting;

use ArtisanPack\Accessibility\Models\ComplianceReport;

class CertificateGenerator
{
    public function generate(ComplianceReport $report): string
    {
        $html = view('accessibility::certificate', ['report' => $report])->render();

        // PDF generation logic will go here.
        // We will need to add a library like DomPDF or Snappy.

        return $html; // For now, just return the HTML.
    }
}
