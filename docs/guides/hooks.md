---
title: Hooks
---

# Hooks (2.3.0+)

Starting in 2.3.0, this package ships three filter hooks (via `artisanpack-ui/hooks`) that let applications customize the color-contrast pipeline without patching the package. Hooks are registered with `addFilter()` and fired via `Hooks::filter()` from inside the package.

## Available Hooks

| Hook | Type | Payload | Purpose |
|------|------|---------|---------|
| `ap.accessibility.contrastThreshold` | filter | `(float $ratio, string $context)` | Override the default WCAG threshold. Context is one of `aa`, `aa-large`, `aaa`, `aaa-large`, `non-text`. |
| `ap.accessibility.contrastColorMap` | filter | `(array $map)` | Register custom Tailwind-name → hex mappings or replace the map entirely. |
| `ap.accessibility.textColorGenerated` | filter | `(string $color, string $background, bool $tinted)` | Take a final say on the generated text color. Fires at every return path (including cached results). |

## `ap.accessibility.contrastThreshold`

Fires inside `WcagValidator::checkContrast()` right before the ratio comparison. Return a different float to bump every check globally — useful when an application wants to force AAA thresholds everywhere without touching call sites.

```php
addFilter('ap.accessibility.contrastThreshold', fn (float $ratio, string $context) => match ($context) {
    'aa', 'aaa'             => 7.0,
    'aa-large', 'aaa-large' => 4.5,
    default                 => $ratio,
});
```

## `ap.accessibility.contrastColorMap`

Fires the first time `AccessibleColorGenerator` loads its Tailwind name → hex map. Use it to register brand colors that the default Tailwind palette does not cover, or to replace the map entirely with a custom design-system palette.

```php
addFilter('ap.accessibility.contrastColorMap', function (array $map) {
    $map['brand-primary'] = '#123456';
    $map['brand-accent']  = '#abcdef';

    return $map;
});
```

## `ap.accessibility.textColorGenerated`

Fires at every return path of `AccessibleColorGenerator::generateAccessibleTextColor()`, including cached results. This is the escape hatch for per-background overrides when the algorithm's answer isn't what you want.

```php
addFilter('ap.accessibility.textColorGenerated', function (string $color, string $background, bool $tinted) {
    return $background === '#123456' ? '#fefefe' : $color;
});
```

## Where to Register

Register hooks in a service provider's `boot()` method so they are in place before any accessibility calls are made:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AccessibilityHooksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        addFilter('ap.accessibility.contrastThreshold', /* ... */);
        addFilter('ap.accessibility.contrastColorMap', /* ... */);
        addFilter('ap.accessibility.textColorGenerated', /* ... */);
    }
}
```

See the [artisanpack-ui/hooks documentation](https://github.com/ArtisanPack-UI/hooks) for the full hook API, including priorities and removal.
