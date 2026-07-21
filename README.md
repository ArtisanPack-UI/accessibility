# ArtisanPack UI Accessibility

A PHP package for ensuring web applications meet accessibility standards, particularly for color contrast.

## Features

- **Color Contrast Checking**: Determine if text colors have sufficient contrast against background colors
- **Accessible Text Color Generation**: Generate accessible text colors based on background colors
- **Color Format Support**: Support for hex, Tailwind CSS, `rgb()`, and `hsl()` color formats.
- **User Accessibility Settings**: Manage user preferences for accessibility features
- **Laravel Integration**: Seamless integration with Laravel applications

## Installation

You can install the package via Composer:

```bash
composer require artisanpack-ui/accessibility
```

For detailed installation instructions, including Laravel integration, see the [Getting Started Guide](docs/getting-started.md).

## Usage

Here is a practical, real-world example of how to use the package to create an accessible button component in Laravel.

### Creating an Accessible Button Component

**1. Create a new Blade component:**

```bash
php artisan make:component Button
```

**2. Modify the component class:**

In `app/View/Components/Button.php`, we'll accept a background color and automatically determine the correct text color.

```php
// app/View/Components/Button.php

namespace App\View\Components;

use Illuminate\View\Component;

class Button extends Component
{
    public string $bgColor;
    public string $textColor;

    public function __construct(string $bgColor = '#3b82f6')
    {
        $this->bgColor = $bgColor;
        $this->textColor = a11yGetContrastColor($this->bgColor);
    }

    public function render()
    {
        return view('components.button');
    }
}
```

**3. Update the component view:**

In `resources/views/components/button.blade.php`, we'll apply the colors as inline styles.

```blade
// resources/views/components/button.blade.php

@props(['bgColor', 'textColor'])

<button {{ $attributes->merge(['class' => 'px-4 py-2 rounded']) }} style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    {{ $slot }}
</button>
```

**4. Use the component in your views:**

Now you can easily create accessible buttons with any background color.

```blade
<x-button>Click Me</x-button>

<x-button bg-color="#dc2626">Delete</x-button>

<x-button bg-color="rgb(16, 185, 129)">Success</x-button>
```

For more detailed examples, including Livewire components and dynamic theming, see the [Real-World Examples](docs/examples.md) documentation.

## Hooks

This package ships with three filter hooks (via `artisanpack-ui/hooks`) that let applications customize the color-contrast pipeline without patching the package:

| Hook                                     | Type   | Payload                                                | Purpose                                                                                                    |
|------------------------------------------|--------|--------------------------------------------------------|------------------------------------------------------------------------------------------------------------|
| `ap.accessibility.contrastThreshold`     | filter | `(float $ratio, string $context)`                      | Override the default WCAG threshold. Context is one of `aa`, `aa-large`, `aaa`, `aaa-large`, `non-text`.   |
| `ap.accessibility.contrastColorMap`      | filter | `(array $map)`                                         | Register custom Tailwind-name → hex mappings or replace the map entirely.                                  |
| `ap.accessibility.textColorGenerated`    | filter | `(string $color, string $background, bool $tinted)`    | Take a final say on the generated text color. Useful for per-background overrides.                         |

```php
// Force AAA thresholds everywhere.
addFilter('ap.accessibility.contrastThreshold', fn (float $ratio, string $context) => match ($context) {
    'aa', 'aaa'             => 7.0,
    'aa-large', 'aaa-large' => 4.5,
    default                 => $ratio,
});

// Add brand colors that the Tailwind palette does not cover.
addFilter('ap.accessibility.contrastColorMap', function (array $map) {
    $map['brand-primary'] = '#123456';

    return $map;
});

// Force a specific text color for a specific background.
addFilter('ap.accessibility.textColorGenerated', function (string $color, string $background, bool $tinted) {
    return $background === '#123456' ? '#fefefe' : $color;
});
```

## AI features

When `artisanpack-ui/ai` v1.0+ is installed alongside this package, three AI-powered accessibility agents become available. Each is toggle-able through the shared `FeatureRegistry` and no-ops when the toggle is off.

| Feature key                 | Agent                              | Purpose                                                                                                    |
|-----------------------------|------------------------------------|------------------------------------------------------------------------------------------------------------|
| `a11y.content_analysis`     | `ContentAccessibilityAgent`        | Finds content-level issues (ambiguous link text, vague headings, undefined jargon) that static rules miss. |
| `a11y.aria_suggestion`      | `AriaSuggestionAgent`              | Suggests ARIA roles, states, and properties for custom components from their markup and behavior.          |
| `a11y.contrast_explanation` | `ColorContrastExplanationAgent`    | Explains contrast failures in plain language and proposes alternatives that preserve brand intent.         |

Trigger surfaces ship in-package for all three frontends so extending framework support does not require any changes to `@artisanpack-ui/react` or `@artisanpack-ui/vue`:

- **Livewire** — `<livewire:a11y-ai-content-analysis />`, `<livewire:a11y-ai-aria-suggestion />`, `<livewire:a11y-ai-contrast-explanation />`.
- **React** — TypeScript/TSX components at `resources/js/react/` (barrel exports through `resources/js/react/index.ts`). Copy into your app's asset pipeline or wire directly through your Vite/Webpack config.
- **Vue** — the same three components as Vue 3 SFCs at `resources/js/vue/`.

The React and Vue components POST to `/api/v1/a11y/ai/{content-analysis,aria-suggestion,contrast-explanation}` by default; pass an `endpoint` prop to override.

The shipped endpoints sit behind `auth:sanctum` and `throttle:api`, so a stateful SPA needs Laravel Sanctum's SPA setup: `SANCTUM_STATEFUL_DOMAINS` configured for the frontend origin, a prior GET to `/sanctum/csrf-cookie` to seed the `XSRF-TOKEN` cookie, and same-origin requests. The bundled React and Vue triggers automatically read that cookie and send `X-XSRF-TOKEN` + `X-Requested-With: XMLHttpRequest`, so no per-caller header wiring is needed once the SPA is authenticated.

## Documentation

For complete documentation, please visit the links below.

- [**Real-World Examples**](docs/examples.md) - Practical examples of Blade and Livewire components.
- [**Best Practices**](docs/best-practices.md) - Learn how to use the package efficiently and performantly.
- [**Migration and Advanced Usage**](docs/migration.md) - Guide for migrating to a more advanced architecture.
- [**Troubleshooting**](docs/troubleshooting.md) - Solutions for common issues.
- [**API Reference**](docs/reference/api-reference.md) - Complete technical documentation.

## Requirements

- PHP 8.3 or higher
- Laravel 12 or 13 (for Laravel integration)

> The PHP floor moved to 8.3 in **2.2.0** to match the `artisanpack-ui/ai` foundation dependency. Laravel 11 support was dropped in the same release. Users staying on PHP 8.2 or Laravel 11 should pin to `^2.1.2`.

## Contributing

As an open source project, this package is open to contributions from anyone. Please [read through the contributing
guidelines](CONTRIBUTING.md) to learn more about how you can contribute to this project.

## License

This package is open-sourced software licensed under the [GPL-3.0-or-later license](LICENSE).
