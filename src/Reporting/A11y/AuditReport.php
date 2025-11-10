<?php

namespace ArtisanPack\Accessibility\Reporting\A11y;

class AuditReport
{
    public array $metadata = [];
    /** @var Finding[] */
    public array $findings = [];

    public function __construct(array $metadata = [])
    {
        $this->metadata = array_merge([
            'startedAt' => date('c'),
            'durationMs' => 0,
            'strictness' => 'AA',
            'totals' => [
                'files' => 0,
                'checks' => 0,
                'passes' => 0,
                'violations' => 0,
                'warnings' => 0,
            ],
        ], $metadata);
        $this->metadata['_started'] = microtime(true);
    }

    public function addFinding(Finding $finding): void
    {
        $this->findings[] = $finding;
    }

    public function finalize(): void
    {
        $passes = 0; $violations = 0; $warnings = 0;
        $files = [];
        foreach ($this->findings as $f) {
            $files[$f->file] = true;
            if ($f->severity === 'violation') $violations++; elseif ($f->severity === 'warning') $warnings++; else $passes++;
        }
        $this->metadata['totals'] = [
            'files' => count($files),
            'checks' => count($this->findings),
            'passes' => $passes,
            'violations' => $violations,
            'warnings' => $warnings,
        ];
        $this->metadata['durationMs'] = (int)round((microtime(true) - ($this->metadata['_started'] ?? microtime(true))) * 1000);
        unset($this->metadata['_started']);
    }

    public function toArray(): array
    {
        return [
            'metadata' => $this->metadata,
            'findings' => array_map(fn($f) => $f->toArray(), $this->findings),
        ];
    }
}
