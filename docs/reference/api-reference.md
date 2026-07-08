---
title: API Reference
---

# API Reference

This document provides a comprehensive reference for all classes, methods, and functions available in the ArtisanPack UI Accessibility package.

## A11y Class

The main accessibility utility class that provides methods for determining appropriate text colors based on background colors, checking contrast ratios, and managing accessibility-related user settings.

### Methods

#### `a11yCSSVarBlackOrWhite(string $hexColor): string`

Analyzes the provided hex color and determines if black or white text would provide better contrast against it.

**Parameters:**
- `$hexColor` (string): The hex code for the background color.

**Returns:**
- (string): Either 'black' or 'white' as a string.

**Example:**
```php
$a11y = new ArtisanPack\Accessibility\Core\A11y();
$textColor = $a11y->a11yCSSVarBlackOrWhite('#3b82f6'); // Returns 'black' or 'white'
```

#### `a11yGetContrastColor(string $hexColor): string`

Calculates the contrast ratio between the background color and both black and white, then returns the hex code for the color (black or white) with better contrast.

**Parameters:**
- `$hexColor` (string): The hex code for the background color.

**Returns:**
- (string): The hex code for either black (#000000) or white (#FFFFFF).

**Example:**
```php
$a11y = new ArtisanPackUI\Accessibility\A11y();
$hexColor = $a11y->a11yGetContrastColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'
```

#### `a11yCheckContrastColor(string $firstHexColor, string $secondHexColor): bool`

Calculates the contrast ratio between two colors according to WCAG 2.0 guidelines. Returns true if the contrast ratio is at least 4.5:1, which is the minimum recommended for normal text to be considered accessible.

**Parameters:**
- `$firstHexColor` (string): The first color to check (hex format).
- `$secondHexColor` (string): The second color to check (hex format).

**Returns:**
- (bool): True if contrast is sufficient (≥4.5:1), false otherwise.

**Example:**
```php
$a11y = new ArtisanPackUI\Accessibility\A11y();
$hasGoodContrast = $a11y->a11yCheckContrastColor('#3b82f6', '#ffffff'); // Returns true or false
```

## AccessibleColorGenerator Class

Generates accessible text colors based on a background color, which can be provided as either a hex code or a Tailwind CSS color name.

### Methods

#### `__construct()`

Initializes the AccessibleColorGenerator with an instance of the A11y class.

**Example:**
```php
$generator = new ArtisanPackUI\Accessibility\AccessibleColorGenerator();
```

#### `generateAccessibleTextColor(string $backgroundColor, bool $tint = false): string`

Determines the best-contrasting text color. It can return either black or white, or it can generate a lighter/darker shade of the original background color that meets accessibility standards.

**Parameters:**
- `$backgroundColor` (string): The background color. Can be a hex code (e.g., '#3b82f6') or a Tailwind color name (e.g., 'blue-500').
- `$tint` (bool, optional): If true, generates an accessible tint or shade. If false, returns black or white. Default false.

**Returns:**
- (string): The generated accessible hex color string.

**Example:**
```php
$generator = new ArtisanPackUI\Accessibility\AccessibleColorGenerator();
$textColor = $generator->generateAccessibleTextColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'
$tintedColor = $generator->generateAccessibleTextColor('#3b82f6', true); // Returns a tinted/shaded hex color
```

## A11yServiceProvider Class

Service provider for the Accessibility package. This class registers the A11y service as a singleton in the Laravel service container, making it available throughout the application.

### Methods

#### `register(): void`

Binds the A11y class to the service container as a singleton with the key 'a11y'.

## A11y Facade

Facade for the A11y class. This facade provides a static interface to the A11y class, allowing for easy access to accessibility methods throughout the application.

### Methods

All methods available on the A11y class can be called statically through the facade.

**Example:**
```php
use ArtisanPackUI\Accessibility\Facades\A11y;

$textColor = A11y::a11yCSSVarBlackOrWhite('#3b82f6');
$hexColor = A11y::a11yGetContrastColor('#3b82f6');
```

## Helper Functions

### `a11y(): A11y`

Get the A11y instance from the service container.

**Returns:**
- (A11y): The A11y service instance.

**Example:**
```php
$a11y = a11y();
$textColor = $a11y->a11yCSSVarBlackOrWhite('#3b82f6');
```

### `a11yCSSVarBlackOrWhite(string $hexColor): string`

Analyzes the provided hex color and determines if black or white text would provide better contrast against it.

**Parameters:**
- `$hexColor` (string): The hex code for the background color.

**Returns:**
- (string): Either 'black' or 'white' as a string.

**Example:**
```php
$textColor = a11yCSSVarBlackOrWhite('#3b82f6'); // Returns 'black' or 'white'
```

### `a11yGetContrastColor(string $hexColor): string`

Calculates the contrast ratio between the background color and both black and white, then returns the hex code for the color (black or white) with better contrast.

**Parameters:**
- `$hexColor` (string): The hex code for the background color.

**Returns:**
- (string): The hex code for either black (#000000) or white (#FFFFFF).

**Example:**
```php
$hexColor = a11yGetContrastColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'
```

### `a11yCheckContrastColor(string $firstHexColor, string $secondHexColor): bool`

Calculates the contrast ratio between two colors according to WCAG 2.0 guidelines. Returns true if the contrast ratio is at least 4.5:1, which is the minimum recommended for normal text to be considered accessible.

**Parameters:**
- `$firstHexColor` (string): The first color to check (hex format).
- `$secondHexColor` (string): The second color to check (hex format).

**Returns:**
- (bool): True if contrast is sufficient (≥4.5:1), false otherwise.

**Example:**
```php
$hasGoodContrast = a11yCheckContrastColor('#3b82f6', '#ffffff'); // Returns true or false
```

### `generateAccessibleTextColor(string $backgroundColor, bool $tint = false): string`

Determines the best-contrasting text color. It can return either black or white, or it can generate a lighter/darker shade of the original background color that meets accessibility standards.

**Parameters:**
- `$backgroundColor` (string): The background color. Can be a hex code (e.g., '#3b82f6') or a Tailwind color name (e.g., 'blue-500').
- `$tint` (bool, optional): If true, generates an accessible tint or shade. If false, returns black or white. Default false.

**Returns:**
- (string): The generated accessible hex color string.

**Example:**
```php
$textColor = generateAccessibleTextColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'
$tintedColor = generateAccessibleTextColor('#3b82f6', true); // Returns a tinted/shaded hex color
```
## AI Agents (2.2.0+)

Three AI-powered agents built on top of `artisanpack-ui/ai` v1.0. See the [AI Features guide](Guides-Ai-Features) for prose walkthroughs, framework surfaces, and Sanctum setup.

Each agent is invoked through the shared `for()`→`run()` pattern inherited from `ArtisanPackUI\Ai\Agents\ArtisanPackAgent`:

```php
$output = SomeAgent::for( $input )->run();
```

### `ArtisanPack\Accessibility\Ai\Agents\ContentAccessibilityAgent`

**Feature key:** `a11y.content_analysis` — **Default model:** `claude-sonnet-4-6`

Finds content-level accessibility issues that static rules miss.

**Input:**

| Key         | Type   | Required | Notes                                                                            |
|-------------|--------|----------|----------------------------------------------------------------------------------|
| `content`   | string | yes      | Plain text, Markdown, or HTML to analyse. Non-empty.                             |
| `structure` | array  | no       | Optional structural summary keyed by `headings`, `links`, `images`.              |

**Output:**

```
{ issues: [ { location: string, issue_type: string, severity: 'info'|'warning'|'error', suggested_fix: string } ] }
```

### `ArtisanPack\Accessibility\Ai\Agents\AriaSuggestionAgent`

**Feature key:** `a11y.aria_suggestion` — **Default model:** `claude-sonnet-4-6`

Suggests ARIA roles, states, properties, and keyboard interactions for a custom component.

**Input:**

| Key             | Type   | Required | Notes                                                          |
|-----------------|--------|----------|----------------------------------------------------------------|
| `markup`        | string | yes      | HTML snippet for the component.                                |
| `behavior`      | string | yes      | Plain-language description of what the component does.         |
| `framework`     | string | no       | Hint at the framework (`livewire`, `react`, `vue`, …).         |
| `existing_aria` | array  | no       | Map of ARIA attributes already present on the markup.          |

**Output:**

```
{
  role:       ?string,             // null when native semantics cover it
  attributes: [ { name: string, value: string, rationale: string } ],
  keyboard:   string[],
  notes:      string[]
}
```

### `ArtisanPack\Accessibility\Ai\Agents\ColorContrastExplanationAgent`

**Feature key:** `a11y.contrast_explanation` — **Default model:** `claude-haiku-4-5`

Explains a failing color pair in plain language and proposes accessible alternatives that preserve brand intent. Contrast math is computed locally via `WcagValidator`; every model-suggested alternative is re-checked and dropped if it still fails.

**Input:**

| Key             | Type   | Required | Notes                                                                   |
|-----------------|--------|----------|-------------------------------------------------------------------------|
| `foreground`    | string | yes      | Hex code or Tailwind color name. Unresolvable inputs throw `FeatureError`. |
| `background`    | string | yes      | Hex code or Tailwind color name.                                        |
| `context`       | string | no       | `body_text` (default, 4.5:1) / `large_text` (3:1) / `ui` (3:1).         |
| `brand_palette` | array  | no       | Optional list of colors the agent should prefer for suggestions.        |

**Output:**

```
{
  explanation:            string,
  current_ratio:          float,
  required_ratio:         float,   // WCAG 2.1 AA
  suggested_alternatives: [ { fg: string, bg: string, ratio: float, delta_from_original: float } ]
}
```

## AI HTTP Controllers (2.2.0+)

All three endpoints are registered under the same `api/v1` prefix as the existing accessibility API, behind `auth:sanctum` + `throttle:api`, and use `FormRequest` classes for validation.

| Method | URI                                     | Controller                                     |
|--------|-----------------------------------------|------------------------------------------------|
| POST   | `/api/v1/a11y/ai/content-analysis`      | `ContentAccessibilityController`               |
| POST   | `/api/v1/a11y/ai/aria-suggestion`       | `AriaSuggestionController`                     |
| POST   | `/api/v1/a11y/ai/contrast-explanation`  | `ColorContrastExplanationController`           |

Response envelope: `{ "data": { … } }` on 200; `{ "error": "…" }` on failure. See the [AI Features guide](Guides-Ai-Features) for the full status-code mapping.

## AI Livewire Components (2.2.0+)

Three components auto-registered by `A11yServiceProvider` when `livewire/livewire` is installed:

| Tag                                     | Component class                                          |
|-----------------------------------------|----------------------------------------------------------|
| `<livewire:a11y-ai-content-analysis />` | `ArtisanPack\Accessibility\Livewire\Ai\ContentAnalysisTrigger` |
| `<livewire:a11y-ai-aria-suggestion />`  | `ArtisanPack\Accessibility\Livewire\Ai\AriaSuggestionTrigger`  |
| `<livewire:a11y-ai-contrast-explanation />` | `ArtisanPack\Accessibility\Livewire\Ai\ContrastExplanationTrigger` |
