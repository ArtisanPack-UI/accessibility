<?php

namespace ArtisanPack\Accessibility\Reporting\A11y;

class HtmlWriter implements ReportWriterInterface
{
    public function write(AuditReport $report, string $outputPath): string
    {
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $data = $report->toArray();
        $meta = $data['metadata'];
        $rows = '';
        foreach ($data['findings'] as $f) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td><span class="swatch" style="background:%s"></span> %s</td><td><span class="swatch" style="background:%s"></span> %s</td><td>%.2f</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars($f['file']),
                htmlspecialchars((string)($f['line'] ?? '')),
                htmlspecialchars($f['foreground']),
                htmlspecialchars($f['foreground']),
                htmlspecialchars($f['background']),
                htmlspecialchars($f['background']),
                $f['ratio'],
                htmlspecialchars($f['severity']),
                htmlspecialchars($f['recommendation']['suggestedForeground'] ?? '')
            );
        }
        $html = '<!doctype html><html><head><meta charset="utf-8"><title>A11y Audit Report</title><style>body{font-family:system-ui, Arial, sans-serif;padding:16px} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ddd;padding:8px;text-align:left} th{background:#f4f4f4} .swatch{display:inline-block;width:16px;height:16px;border:1px solid #ccc;vertical-align:middle;margin-right:6px}</style></head><body>';
        $html .= sprintf('<h1>A11y Audit Report</h1><p>Strictness: %s<br>Started: %s<br>Duration: %d ms</p>',
            htmlspecialchars($meta['strictness']), htmlspecialchars($meta['startedAt']), (int)$meta['durationMs']);
        $tot = $meta['totals'];
        $html .= sprintf('<p>Files: %d | Checks: %d | Passes: %d | Violations: %d | Warnings: %d</p>',
            (int)$tot['files'], (int)$tot['checks'], (int)$tot['passes'], (int)$tot['violations'], (int)$tot['warnings']);
        $html .= '<table><thead><tr><th>File</th><th>Line</th><th>Foreground</th><th>Background</th><th>Ratio</th><th>Severity</th><th>Suggestion</th></tr></thead><tbody>' . $rows . '</tbody></table>';
        $html .= '</body></html>';
        file_put_contents($outputPath, $html);
        return $outputPath;
    }
}
