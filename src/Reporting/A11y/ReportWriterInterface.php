<?php

namespace ArtisanPack\Accessibility\Reporting\A11y;

interface ReportWriterInterface
{
    /**
     * Write the audit report to the given output path (file path).
     * Should return the final path written.
     */
    public function write(AuditReport $report, string $outputPath): string;
}
