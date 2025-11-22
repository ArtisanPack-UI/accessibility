# Digital Shopfront CMS Accessibility Changelog

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
