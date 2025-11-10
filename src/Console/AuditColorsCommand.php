<?php

namespace ArtisanPack\Accessibility\Console;

use ArtisanPack\Accessibility\Auditing\ColorAuditor;
use ArtisanPack\Accessibility\Reporting\A11y\AuditReport;
use ArtisanPack\Accessibility\Reporting\A11y\JsonWriter;
use ArtisanPack\Accessibility\Reporting\A11y\HtmlWriter;
use ArtisanPack\Accessibility\Reporting\A11y\MarkdownWriter;
use Illuminate\Console\Command;

class AuditColorsCommand extends Command
{
    protected $signature = 'a11y:audit-colors
        {--path=* : Paths to scan}
        {--include=* : Include extensions or patterns}
        {--exclude=* : Exclude path substrings}
        {--format=* : Report formats json|html|md}
        {--output= : Output directory or base path}
        {--strictness=AA : A|AA|AAA}
        {--fail-on=violation : none|any|error|violation|threshold:N}
        {--progress : Force show progress}
        {--no-progress : Disable progress}
    ';

    protected $description = 'Audit CSS and Blade templates for color contrast issues.';

    public function handle(): int
    {
        $paths = $this->option('path');
        if (empty($paths)) {
            $paths = config('accessibility.audit.paths', ['resources/views', 'resources/css', 'public/css']);
        }
        $include = $this->option('include') ?: config('accessibility.audit.include_extensions', ['css','blade.php']);
        $exclude = $this->option('exclude') ?: config('accessibility.audit.exclude', ['vendor','storage','node_modules']);
        $formats = $this->option('format') ?: config('accessibility.report.formats', ['md']);
        $outputBase = $this->option('output') ?: config('accessibility.report.output_path', storage_path('app/a11y'));
        $strictness = strtoupper($this->option('strictness') ?: config('accessibility.audit.strictness', 'AA'));
        $progressOpt = $this->option('progress');
        $noProgressOpt = $this->option('no-progress');
        $showProgress = $progressOpt || (config('accessibility.progress.enabled', true) && !$noProgressOpt);

        $auditor = new ColorAuditor();

        $files = $auditor->listFiles($paths, $include, $exclude);
        $total = count($files);
        if ($showProgress && $total >= (int)config('accessibility.progress.min_items_for_bar', 50)) {
            $bar = $this->output->createProgressBar($total);
            $bar->start();
            $report = $auditor->audit($paths, $include, $exclude, $strictness, true, function($i,$t,$phase,$file) use ($bar){
                $bar->advance();
            });
            $bar->finish();
            $this->newLine();
        } else {
            $report = $auditor->audit($paths, $include, $exclude, $strictness, false, null);
        }

        // Console summary
        $meta = $report->metadata;
        $tot = $meta['totals'];
        $this->info(sprintf('A11y Audit complete: Files %d, Checks %d, Passes %d, Violations %d, Warnings %d',
            $tot['files'], $tot['checks'], $tot['passes'], $tot['violations'], $tot['warnings']));

        // Write reports
        $written = [];
        foreach ($formats as $fmt) {
            $fmt = strtolower($fmt);
            $base = rtrim($outputBase, DIRECTORY_SEPARATOR);
            if (is_dir($base)) {
                $path = $base . DIRECTORY_SEPARATOR . 'a11y-audit.' . $fmt;
            } else {
                $path = $base . '.' . $fmt;
            }
            $writer = match ($fmt) {
                'json' => new JsonWriter(),
                'html' => new HtmlWriter(),
                'md', 'markdown' => new MarkdownWriter(),
                default => null,
            };
            if ($writer) {
                $writer->write($report, $path);
                $written[] = $path;
            }
        }

        foreach ($written as $p) {
            $this->line("Wrote report: {$p}");
        }

        // Exit codes
        $failOn = $this->option('fail-on') ?: 'violation';
        $exitCode = 0;
        if ($failOn === 'any' && ($tot['violations'] > 0 || $tot['warnings'] > 0)) {
            $exitCode = 2;
        } elseif ($failOn === 'violation' && $tot['violations'] > 0) {
            $exitCode = 2;
        } elseif (str_starts_with((string)$failOn, 'threshold:')) {
            $n = (int)substr((string)$failOn, strlen('threshold:'));
            if ($tot['violations'] >= $n) $exitCode = 2;
        }

        return $exitCode;
    }
}
