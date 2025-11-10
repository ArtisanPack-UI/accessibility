<?php

namespace ArtisanPack\Accessibility\Reporting\A11y;

class Finding
{
    public string $file;
    public ?int $line;
    public string $context;
    public string $foreground;
    public string $background;
    public float $ratio;
    public string $severity; // violation|warning|info
    public ?array $recommendation;
    public array $tags;

    public function __construct(array $data)
    {
        $this->file = $data['file'] ?? '';
        $this->line = $data['line'] ?? null;
        $this->context = $data['context'] ?? '';
        $this->foreground = $data['foreground'] ?? '';
        $this->background = $data['background'] ?? '';
        $this->ratio = (float)($data['ratio'] ?? 0.0);
        $this->severity = $data['severity'] ?? 'info';
        $this->recommendation = $data['recommendation'] ?? null;
        $this->tags = $data['tags'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'context' => $this->context,
            'foreground' => $this->foreground,
            'background' => $this->background,
            'ratio' => $this->ratio,
            'severity' => $this->severity,
            'recommendation' => $this->recommendation,
            'tags' => $this->tags,
        ];
    }
}
