# Artisan Commands: Accessibility Auditing and Palette Generation

This guide explains how to use the package's Artisan commands to audit color accessibility in your project and generate accessible color palettes.

- a11y:audit-colors — Scan CSS and Blade templates for color contrast issues and generate reports (JSON/HTML/Markdown)
- a11y:generate-palette — Create accessible color palettes from a seed color and export in multiple formats

See also:
- Configuration reference: config/accessibility.php
- Related docs: [Advanced Color Palette Generation](Palette-Generation)

## Prerequisites

- Laravel application with this package installed
- The service provider is auto-discovered: `ArtisanPack\Accessibility\Laravel\A11yServiceProvider`
- Review or publish configuration as needed:

```bash
php artisan vendor:publish --tag=config
# This publishes config/accessibility.php if not already present
```

## a11y:audit-colors

Audit CSS and Blade templates for WCAG color contrast issues.

### Usage

```bash
php artisan a11y:audit-colors [options]
```

### Options

- --path=* — One or more directories or files to scan. Overrides config.
- --include=* — Additional include patterns or extensions (e.g., "resources/**/*.blade.php").
- --exclude=* — Patterns or path substrings to ignore (e.g., "node_modules").
- --format=* — Report formats: json, html, md. Default: from config.
- --output= — Output directory or base file path for reports. Default: from config.
- --strictness= — WCAG level: A|AA|AAA. Default: from config (AA).
- --fail-on= — none|any|error|violation|threshold:N (see Exit Codes). Default: violation.
- --progress — Force-show the progress bar even for small scans.
- --no-progress — Disable the progress bar.

Notes:
- If --output points to an existing directory, files are written as `a11y-audit.<ext>` inside that directory.
- If --output points to a file base path (directory does not exist), each format appends its extension to that path.

### Examples

- Quick scan using defaults from config:

```bash
php artisan a11y:audit-colors
```

- Scan specific paths and write JSON and Markdown reports to storage/app/a11y:

```bash
php artisan a11y:audit-colors \
  --path=resources/views --path=resources/css \
  --format=json --format=md
```

- Output all formats to a custom folder:

```bash
php artisan a11y:audit-colors --format=json --format=html --format=md --output=storage/app/a11y
```

- Fail the build if any violation is found (useful for CI):

```bash
php artisan a11y:audit-colors --fail-on=violation
```

- Fail only if at least N violations are found:

```bash
php artisan a11y:audit-colors --fail-on=threshold:10
```

### Exit Codes

- 0 — Success, and no violations at or above your chosen threshold/policy
- 2 — Violations detected that match your --fail-on policy
- 1 — Runtime error (invalid options, unexpected exceptions)

### Progress Indicators

A progress bar is shown for large scans when running in a TTY (terminal). Control it with:
- --progress to force-show
- --no-progress to disable

### Reports

Supported formats:
- JSON — Programmatic consumption (stable structure)
- HTML — Developer-friendly viewing with summary and color swatches
- Markdown — Concise summary for PR comments and CI artifacts

Default output directory: `storage/app/a11y`
- a11y-audit.json
- a11y-audit.html
- a11y-audit.md

## a11y:generate-palette

Generate an accessible color palette from a primary seed color.

### Usage

```bash
php artisan a11y:generate-palette --primary=#3366FF [options]
```

### Options

- --primary= — Required. Primary seed color (hex).
- --secondary= — Optional secondary seed color (hex).
- --accent=* — Optional additional accent colors.
- --background= — Optional background color to evaluate with --foreground.
- --foreground= — Optional foreground color to evaluate with --background.
- --strictness= — WCAG level: A|AA|AAA. Default AA.
- --size= — Number of tints/shades per palette (default 9).
- --format= — Output format: json|md|html (default md).
- --output= — When provided, writes to the given file path; otherwise prints to stdout.
- --json — Shortcut to emit JSON to stdout.

### Examples

- Print a Markdown palette to stdout:

```bash
php artisan a11y:generate-palette --primary=#0ea5e9
```

- Write an HTML palette file:

```bash
php artisan a11y:generate-palette --primary=#0ea5e9 --format=html --output=storage/app/a11y/palette.html
```

- Emit JSON to stdout (useful in scripts):

```bash
php artisan a11y:generate-palette --primary=#0ea5e9 --json
```

- Optionally include a foreground/background pair to check contrast:

```bash
php artisan a11y:generate-palette --primary=#0ea5e9 \
  --foreground=#111111 --background=#ffffff --format=json
```

## Configuration Reference

The following keys in `config/accessibility.php` affect the commands:

```php
'accessibility' => [
    'audit' => [
        'paths' => [
            base_path('resources/views'),
            base_path('resources/css'),
            base_path('public/css'),
        ],
        'include_extensions' => ['css', 'blade.php'],
        'exclude' => ['vendor', 'storage', 'node_modules'],
        'strictness' => 'AA',
    ],

    'report' => [
        'formats' => ['md'],
        'output_path' => storage_path('app/a11y'),
    ],

    'progress' => [
        'enabled' => true,
        'min_items_for_bar' => 50,
    ],
]
```

Tips:
- Use ENV variables to override thresholds and cache if needed (see wcag_thresholds and cache keys in the same config file).
- You can still override all of these at runtime using command options.

## CI Integration

To enforce accessibility budgets in CI:

```bash
# Example: fail the job if any violation is found
php artisan a11y:audit-colors --fail-on=violation --format=json --output=storage/app/a11y

# Upload storage/app/a11y/a11y-audit.json as a build artifact for review
```

Common patterns:
- Schedule regular audits and track artifacts in your pipeline.
- Use Markdown reports for quick summaries in PRs.

## Troubleshooting

- Command not found: Ensure dependencies are installed and run `php artisan list` to verify commands are registered.
- No files found: Adjust `--path`, `--include`, and `--exclude` options or update config.
- Output path errors: Ensure the directory exists or set `--output` to a writable location; by default, `storage/app/a11y` is used.
- Unexpected results in Blade: The static analysis uses heuristics for Tailwind classes; consider adding more explicit styles or testing directly with CSS.

## Changelog and Support

- See CHANGELOG.md for release notes.
- File issues or feature requests on the project repository.