# Digital Shopfront CMS Accessibility Changelog

## [Unreleased]

## [2.1.2] - 2026-06-08

This release adds Laravel 13 support alongside the existing Laravel 11 and 12 compatibility, and modernizes the release pipeline so tagged versions publish to Packagist automatically.

### Added
- Added Laravel 13 support. The `illuminate/support` framework constraint now resolves against `^11.44.1|^12.0|^13.0`. The Laravel 11 lower bound is `11.44.1` to exclude versions covered by published security advisories on the framework; users staying on Laravel 11 should already be on `11.44.1` or later. Laravel 13 is only selectable on PHP 8.3+ via Laravel's own constraint — no PHP-floor bump is required.
- Added a CI matrix that runs the test suite against Laravel 11, 12, and 13 on every supported PHP version (8.2 for L11/12, 8.3+ for L13).
- Added a dedicated `.github/workflows/release.yml` that triggers on `v*` tag pushes, runs the pre-release test suite, creates the GitHub Release with notes extracted from this changelog, and notifies the Packagist update-package API so the new version publishes without manual intervention.

### Changed
- Promoted `illuminate/support` from `suggest` to a hard `require` so the multi-version framework constraint is enforced at install time. The Laravel integration layer (service provider, facade, HTTP/API controllers) has always required `illuminate/support` at runtime.
- Widened dev dependency constraints to cover the Laravel 13 toolchain: `orchestra/testbench` to `^9.0|^10.2|^11.0`, `pestphp/pest` to `^3.8|^4.0`, and `pestphp/pest-plugin-laravel` to `^3.1|^4.0`. The lock file still resolves to the Laravel 12 toolchain for local development; the CI matrix overrides per-row to exercise the other framework versions.
- Updated supported-versions notes in `README.md` and `docs/guides/getting-started.md` to reflect Laravel 11/12/13.
- Removed Laravel 5.x manual-registration instructions from `docs/guides/getting-started.md` (auto-discovery is automatic on every supported Laravel version) and corrected the documented service-provider and facade class names to match the actual namespaces. The new manual-registration example uses `AliasLoader::getInstance()->alias()` from a service provider's `register()` method, which is the idiomatic approach in the streamlined Laravel 11+ app structure.
- Removed the inline release job from `.github/workflows/ci.yml` and dropped its tag trigger; `ci.yml` now only fires on pushes to `main` and pull requests targeting `main`.

## [2.1.1] - 2026-01-09

This release integrates the package with the core ArtisanPack UI package for a unified configuration structure.

### Changed
- Updated configuration namespace from `accessibility.*` to `artisanpack.accessibility.*` to align with the ArtisanPack UI ecosystem
- Published config file path changed from `config/accessibility.php` to `config/artisanpack/accessibility.php`
- Changed config publish tag from `config` to `artisanpack-package-config`

### Added
- Added `mergeConfiguration()` method in service provider to properly merge package defaults with user customizations

### Migration notes
- Update any references to `config('accessibility.*')` to use `config('artisanpack.accessibility.*')` instead
- If you have a published config file at `config/accessibility.php`, move it to `config/artisanpack/accessibility.php`

## [2.1.0] - 2025-11-22

This release focuses on developer experience improvements, including code style tooling, CI/CD enhancements, and documentation cleanup.

### Added
- Added Laravel Pint (v1.25+) as a dev dependency for code style checking
- Added `artisanpack-ui/code-style-pint` (v1.0+) for standardized code style rules
- Added Laravel Boost guidelines file (`resources/boost/guidelines/core.blade.php`) for AI-assisted development

### Changed
- Updated GitLab CI build job to use php:8.2-cli image for consistency with test and benchmark jobs
- Updated AI guidelines documentation for improved clarity

### Fixed
- Fixed GitLab CI pipeline build failures caused by PHP 8.5 compatibility issues
- Resolved Symfony HTTP Foundation security vulnerability (PKSA-365x-2zjk-pt47) by requiring symfony/http-foundation ^7.3.7

### Removed
- Removed all documentation references to the `getToastDuration()` function which was not implemented in the codebase

## [2.0.0] - 2025-11-10

This is a major release with a redesigned core, an expanded plugin system, first‑class reporting, theming and palette generation features, and a new HTTP/Laravel integration layer. It includes breaking changes for plugin authors and anyone consuming internal classes that were reorganized.

### Breaking changes
- Plugin API overhauled: new contracts introduced under `src/Plugins/Contracts` (e.g., `PluginInterface`, `PluginMetadata`, `Capability`, `Context`, `Report`, `ResultSet`, `AccessibilityRulePluginInterface`, `AnalysisToolPluginInterface`, `ColorFormatPluginInterface`). Existing plugins must be updated to the new interfaces and the new `ResultSet` shape.
- PluginManager refactored. Registration/lookup behavior has been unified and example plugins have been updated accordingly.
- Core namespaces reorganized. Core functionality now lives under `src/Core`; Laravel-specific integration under `src/Laravel`. Update any imports that referenced moved classes.
- Configuration structure updated. New config files `config/accessibility.php` and `config/plugins.php` were added; review and publish them if you use Laravel.

### Added
- Core analysis modules: `AccessibilityScorer`, `PerceptualAnalyzer`, `ColorBlindnessSimulator`, `ReportGenerator`, `WcagValidator`, constants and helpers.
- Caching subsystem: `ArrayCache`, `FileCache`, `NullCache`, and `CacheManager`.
- Palette generation suite: `PaletteGenerator`, `ColorHarmony`, and export formats (`CssExporter`, `JsonExporter`, `ScssExporter`, `TailwindExporter`) plus `resources/tailwind-colors.php`.
- Theming utilities: `ThemeGenerator`, `ThemeValidator`, `CssVariableParser`.
- Reporting platform: A11y report writers (`HtmlWriter`, `JsonWriter`, `MarkdownWriter`), `ComplianceReporter`, `ComplianceMonitor`, `TrendAnalyzer`, `AccessManager`, `Dashboard`, `TeamManager`, `CertificateGenerator`, `AuditTrail` model and services.
- HTTP API and Laravel integration: routes (`routes/api.php`), controller (`A11yApiController`), form requests, Laravel service provider/facade, and a Blade view for certificates.
- Events and listeners for auditing and cache instrumentation.
- CLI commands: `audit:colors` and `palette:generate` (see docs/commands.md).
- Plugin examples: `ContrastRulePlugin`, updated `ColorFormatHexPlugin`, and `LinksAnalysisTool` with `plugin.json` manifests.
- Database: new enterprise tables migration (2025_11_09_000000_create_enterprise_tables.php) and model factories.
- Documentation: extensive guides and reference under `docs/` (API, plugins, palette generation, theming, performance, WCAG compliance, framework‑agnostic usage, etc.).
- Tests: large expansion of unit/feature/performance tests to cover the new surface area.

### Changed
- Composer configuration updated; autoloading and package metadata refined.
- CI configuration updated (`.gitlab-ci.yml`).
- Example plugins and reporting components refined and stabilized.

### Fixed
- Minor fixes and refinements across example plugins and reporting (e.g., Dashboard/Team management flows).

### Migration notes
- Update custom plugins to implement the new contracts and return the new `ResultSet` structure.
- If you referenced classes that moved into `src/Core` or `src/Laravel`, update imports accordingly.
- For Laravel users: publish the new config files, register the package service provider if necessary, and run the new database migration.
- Review the updated docs under `docs/` for details and examples.

## 1.1.1 - July 1, 2025

- Added in a method to set the correct text color based on background for color contrast.

## 1.0.3 - May 14, 2025

- Fixed issue with the renaming process.

## 1.0.2 - May 14, 2025

- Changed the vendor name to ArtisanPack UI.

## 1.0.1 - April 20, 2025

- Removed unnecessary files from the published package.

## 1.0 - April 16, 2025

- Initial release
