<?php

namespace ArtisanPack\Accessibility\Auditing;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\WcagValidator;
use ArtisanPack\Accessibility\Reporting\A11y\AuditReport;
use ArtisanPack\Accessibility\Reporting\A11y\Finding;
use SplFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class ColorAuditor
{
    public function __construct(
        protected WcagValidator $validator = new WcagValidator(),
        protected ?AccessibleColorGenerator $suggestionEngine = null
    ) {
        $this->suggestionEngine = $suggestionEngine ?? new AccessibleColorGenerator($this->validator);
    }

    /**
     * Run audit on given paths.
     * @param array $paths
     * @param array $includeExtensions e.g., ['css','blade.php']
     * @param array $excludePatterns e.g., ['vendor','storage']
     * @param string $strictness 'A'|'AA'|'AAA'
     * @param bool $progress
     * @param callable|null $progressCb function(int $current, int $total, string $phase, ?string $file)
     * @return AuditReport
     */
    public function audit(
        array $paths,
        array $includeExtensions = ['css','blade.php'],
        array $excludePatterns = ['vendor','storage','node_modules'],
        string $strictness = 'AA',
        bool $progress = false,
        ?callable $progressCb = null
    ): AuditReport {
        $report = new AuditReport([
            'strictness' => strtoupper($strictness),
        ]);

        $files = $this->discoverFiles($paths, $includeExtensions, $excludePatterns);
        $total = count($files);
        $i = 0;
        foreach ($files as $file) {
            $i++;
            if ($progress && $progressCb) {
                $progressCb($i, $total, 'analyze', $file->getPathname());
            }
            $ext = $file->getExtension();
            $path = $file->getPathname();
            if ($ext === 'css') {
                $findings = $this->analyzeCss($path, $strictness);
            } elseif ($ext === 'php' && str_ends_with($path, '.blade.php')) {
                $findings = $this->analyzeBlade($path, $strictness);
            } else {
                $findings = [];
            }
            foreach ($findings as $finding) {
                $report->addFinding($finding);
            }
        }

        $report->finalize();
        return $report;
    }

    /**
     * @param array $paths
     * @param array $includeExtensions
     * @param array $excludePatterns
     * @return SplFileInfo[]
     */
    public function listFiles(array $paths, array $includeExtensions, array $excludePatterns): array
    {
        return $this->discoverFiles($paths, $includeExtensions, $excludePatterns);
    }

    protected function discoverFiles(array $paths, array $includeExtensions, array $excludePatterns): array
    {
        $files = [];
        $extensions = array_map(fn($e) => ltrim($e, '.'), $includeExtensions);
        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = new SplFileInfo($path);
                continue;
            }
            if (!is_dir($path)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;
                $p = $file->getPathname();
                if ($this->isExcluded($p, $excludePatterns)) continue;
                $ext = $file->getExtension();
                if (in_array($ext, $extensions, true)) {
                    $files[] = $file;
                } elseif ($ext === 'php' && str_ends_with($p, '.blade.php') && in_array('blade.php', $extensions, true)) {
                    $files[] = $file;
                }
            }
        }
        return $files;
    }

    protected function isExcluded(string $path, array $excludePatterns): bool
    {
        foreach ($excludePatterns as $pattern) {
            if ($pattern && str_contains($path, $pattern)) return true;
        }
        return false;
    }

    /**
     * Very simple CSS analyzer: pairs color and background/background-color in same rule block.
     * @param string $path
     * @param string $strictness
     * @return Finding[]
     */
    protected function analyzeCss(string $path, string $strictness): array
    {
        $content = @file_get_contents($path) ?: '';
        $results = [];
        // Match blocks selector { ... }
        if (preg_match_all('/([^{]+)\{([^}]+)\}/m', $content, $blocks, PREG_SET_ORDER)) {
            foreach ($blocks as $block) {
                $selector = trim($block[1]);
                $body = $block[2];
                $fg = $this->extractCssProperty($body, 'color');
                // avoid matching 'border-color' when extracting 'color' only
                if ($fg && preg_match('/border\s*\-|outline\s*\-|stroke|fill/i', $body)) {
                    // keep anyway; CSS can have multiple color uses; we only check text color basics
                }
                $bg = $this->extractCssProperty($body, 'background-color') ?? $this->extractCssProperty($body, 'background');
                if ($fg && $bg) {
                    $results[] = $this->buildFinding($path, $selector, $fg, $bg, $strictness, null);
                }
            }
        }
        return $results;
    }

    /**
     * Very simple Blade analyzer: looks for inline styles with color/background and hex/rgb in class attributes.
     * @param string $path
     * @param string $strictness
     * @return Finding[]
     */
    protected function analyzeBlade(string $path, string $strictness): array
    {
        $content = @file_get_contents($path) ?: '';
        $results = [];
        // Inline styles
        if (preg_match_all('/style\s*=\s*"([^"]+)"/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $style = $m[1];
                $fg = $this->extractCssProperty($style, 'color');
                $bg = $this->extractCssProperty($style, 'background-color') ?? $this->extractCssProperty($style, 'background');
                if ($fg && $bg) {
                    $results[] = $this->buildFinding($path, '<inline-style>', $fg, $bg, $strictness, null);
                }
            }
        }
        // Hex colors inside template with both fg and bg close by (heuristic: within same tag)
        if (preg_match_all('/<[^>]+>/m', $content, $tags)) {
            foreach ($tags[0] as $tag) {
                $fg = $this->extractCssProperty($tag, 'color');
                $bg = $this->extractCssProperty($tag, 'background-color') ?? $this->extractCssProperty($tag, 'background');
                if ($fg && $bg) {
                    $results[] = $this->buildFinding($path, '<tag>', $fg, $bg, $strictness, null);
                }
            }
        }
        return $results;
    }

    protected function extractCssProperty(string $css, string $property): ?string
    {
        if (preg_match('/' . preg_quote($property, '/') . '\s*:\s*([^;\n]+)\s*;?/i', $css, $m)) {
            $value = trim($m[1]);
            // Extract first color-like token
            $color = $this->normalizeColor($value);
            return $color ?: null;
        }
        return null;
    }

    protected function normalizeColor(string $value): ?string
    {
        $value = trim($value);
        // hex
        if (preg_match('/#([a-f0-9]{3}|[a-f0-9]{6})/i', $value, $m)) {
            $hex = $m[0];
            // normalize short to long
            if (strlen($hex) === 4) {
                $hex = sprintf('#%1$s%1$s%2$s%2$s%3$s%3$s', $hex[1], $hex[2], $hex[3]);
            }
            return strtoupper($hex);
        }
        // rgb or rgba
        if (preg_match('/rgba?\(([^\)]+)\)/i', $value, $m)) {
            $parts = array_map('trim', explode(',', $m[1]));
            if (count($parts) >= 3) {
                $r = (int)$parts[0]; $g = (int)$parts[1]; $b = (int)$parts[2];
                return sprintf('#%02X%02X%02X', max(0,min(255,$r)), max(0,min(255,$g)), max(0,min(255,$b)));
            }
        }
        return null;
    }

    protected function buildFinding(string $file, string $context, string $foreground, string $background, string $strictness, ?int $line): Finding
    {
        $ratio = $this->validator->calculateContrastRatio($foreground, $background);
        $passes = $this->validator->checkContrast($foreground, $background, $strictness, false);
        $recommendation = null;
        if (!$passes) {
            // Suggest adjusted foreground against background
            $suggested = $this->suggestionEngine->generateAccessibleTextColor($background, true, $strictness, false);
            $recommendation = [
                'message' => 'Adjust foreground color to meet contrast',
                'suggestedForeground' => $suggested,
            ];
        }
        return new Finding([
            'file' => $file,
            'line' => $line,
            'context' => $context,
            'foreground' => strtoupper($foreground),
            'background' => strtoupper($background),
            'ratio' => $ratio,
            'severity' => $passes ? 'info' : 'violation',
            'recommendation' => $recommendation,
            'tags' => ['contrast'],
        ]);
    }
}
