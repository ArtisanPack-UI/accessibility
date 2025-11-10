<?php

namespace ArtisanPack\Accessibility\Reporting\A11y;

class JsonWriter implements ReportWriterInterface
{
    public function write(AuditReport $report, string $outputPath): string
    {
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $payload = json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($outputPath, $payload);
        return $outputPath;
    }
}
