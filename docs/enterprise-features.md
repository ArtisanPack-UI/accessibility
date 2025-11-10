# Enterprise Reporting Features

This document explains how to use the enterprise reporting features introduced in the Accessibility package. It covers setup, migrations, core services, and examples.

## 1. Setup

- Ensure the service provider is registered (done automatically by the package in tests):
  - ArtisanPack\Accessibility\Laravel\A11yServiceProvider
- Publish or review configuration at `config/accessibility.php`.
- Roles and permissions are configurable under the `roles` key.

## 2. Database Migrations

The package ships a migration (`2025_11_09_000000_create_enterprise_tables.php`) that creates:
- organizations
- compliance_reports
- audit_trails
- teams

Run migrations in your Laravel app:

```
php artisan migrate
```

## 3. Models

- Organization: hasMany ComplianceReport, hasMany Team
- ComplianceReport: belongsTo Organization; `issues` is cast to array
- AuditTrail: belongsTo User model configured at `auth.providers.users.model`
- Team: belongsTo Organization and User

## 4. Services

### 4.1 ComplianceReporter
Generate and persist a compliance report using AccessibilityScorer.

Example:

```
use ArtisanPack\Accessibility\Reporting\ComplianceReporter;
use ArtisanPack\Accessibility\Core\Analysis\AccessibilityScorer;

$reporter = app()->makeWith(ComplianceReporter::class, [
  'scorer' => app(AccessibilityScorer::class),
]);

$report = $reporter->generate('#ffffff', '#000000', $organizationId);
// $report->score (int), $report->issues (array)
```

### 4.2 AuditTrail
Log actions and listen to events.

```
use ArtisanPack\Accessibility\Reporting\AuditTrail;

app(AuditTrail::class)->log('color_contrast_checked', [
  'color1' => '#ffffff',
  'color2' => '#000000',
  'level' => 'AA',
  'is_large_text' => false,
  'result' => true,
]);
```

The package listener `LogColorContrastCheck` automatically logs events from `ColorContrastChecked` dispatched by `WcagValidator`.

### 4.3 Dashboard
Aggregate basic dashboard metrics for an organization.

```
use ArtisanPack\Accessibility\Reporting\Dashboard;

$data = app(Dashboard::class)->getData($organizationId);
// ['total_reports' => int, 'average_score' => float|null]
```

### 4.4 TrendAnalyzer
Return time-ordered score data points for an organization within a date range.

```
use ArtisanPack\Accessibility\Reporting\TrendAnalyzer;

$trend = app(TrendAnalyzer::class)->analyze($organizationId, days: 30);
// [ ['score' => 85, 'date' => '2025-11-01'], ... ]
```

### 4.5 CertificateGenerator
Render the certificate HTML (you can integrate DomPDF/Snappy to export a PDF).

```
use ArtisanPack\Accessibility\Reporting\CertificateGenerator;

$html = app(CertificateGenerator::class)->generate($report);
```

### 4.6 TeamManager
Manage organization members and roles.

```
use ArtisanPack\Accessibility\Reporting\TeamManager;
use Tests\User; // Replace with your App\Models\User in real app

$teamManager = app(TeamManager::class);
$team = $teamManager->inviteUser($organization, $user, 'editor');
$teamManager->removeUser($team);
$users = $teamManager->listUsers($organization);
```

### 4.7 AccessManager
Check role-based permissions using roles in config.

```
use ArtisanPack\Accessibility\Reporting\AccessManager;

$canView = app(AccessManager::class)->can($user, 'view_reports', $organization);
```

## 5. API Endpoints

The package registers API routes under `routes/api.php` for contrast checks and color generation. Dashboard and reporting APIs can be added similarly in your host app using the provided services.

## 6. Notes

- Ensure authentication is configured; AuditTrail uses the current authenticated user (`Auth::id()`).
- Customize roles and permissions in `config/accessibility.php` as needed.
- To automate monitoring, create a scheduled command that uses `ComplianceMonitor` to run reports periodically.
