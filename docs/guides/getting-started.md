---
title: Getting Started with ArtisanPack UI Accessibility
---

# Getting Started with ArtisanPack UI Accessibility

This guide will help you get started with the ArtisanPack UI Accessibility package, which provides tools for ensuring your web applications meet accessibility standards, particularly for color contrast.

## Requirements

- PHP 8.3 or higher
- Laravel 12 or 13 (for Laravel integration)

> The PHP floor moved to 8.3 in 2.2.0 to match the `artisanpack-ui/ai` foundation dependency. Laravel 11 support was dropped in the same release. Users staying on PHP 8.2 or Laravel 11 should pin to `^2.1.2`.

## Installation

You can install the package via Composer:

```bash
composer require artisanpack-ui/accessibility
```

## Laravel Integration

The `A11yServiceProvider` and the `A11y` facade alias are registered automatically via Laravel's package auto-discovery — no manual registration required.

If you have disabled auto-discovery for this package (for example by listing it in your application's `extra.laravel.dont-discover` array), register the service provider in `bootstrap/providers.php`:

```php
// bootstrap/providers.php
return [
    // ...
    ArtisanPack\Accessibility\Laravel\A11yServiceProvider::class,
];
```

If you also want to use the short `A11y` facade alias, register it via the `AliasLoader` from the `register()` method of one of your application service providers (Laravel 11+ no longer ships an `aliases` array in `config/app.php`):

```php
use ArtisanPack\Accessibility\Laravel\Facades\A11y;
use Illuminate\Foundation\AliasLoader;

public function register(): void
{
    AliasLoader::getInstance()->alias('A11y', A11y::class);
}
```

## Basic Usage

Once installed, you can use the package in several ways:

### Using Helper Functions

The package provides several global helper functions for easy access to accessibility features:

```php
// Check if text should be black or white on a given background color
$textColor = a11yCSSVarBlackOrWhite('#3b82f6'); // Returns 'black' or 'white'

// Get the hex code for the most accessible text color (black or white)
$hexColor = a11yGetContrastColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'

// Generate an accessible text color for a background
$accessibleColor = generateAccessibleTextColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'

// Generate a tinted/shaded version of the color that's accessible
$tintedColor = generateAccessibleTextColor('#3b82f6', true); // Returns a tinted/shaded hex color
```

### Using the A11y Class

You can also use the A11y class directly:

```php
use ArtisanPackUI\Accessibility\A11y;

$a11y = new A11y();
$textColor = $a11y->a11yCSSVarBlackOrWhite('#3b82f6');
$hexColor = $a11y->a11yGetContrastColor('#3b82f6');
```

### Using the Laravel Facade

If you're using Laravel and have registered the facade, you can use it like this:

```php
use A11y;

$textColor = A11y::a11yCSSVarBlackOrWhite('#3b82f6');
$hexColor = A11y::a11yGetContrastColor('#3b82f6');
```

## Next Steps

- Check out the [Usage Guide](Usage) for more detailed examples
- See the [API Reference](Api-Reference) for a complete list of available methods
- Learn about [Tailwind Color Support](Tailwind-Colors) for using Tailwind CSS color names

## Testing

This package uses Pest for its testing suite. You can run the tests by executing the following command from the root of the package:

```bash
composer test
```

### Testing Against Multiple Laravel Versions

To ensure compatibility with different versions of Laravel, you can test the package against multiple Laravel versions by using a CI matrix in your continuous integration pipeline. Tools like `spatie/laravel-test-runner` can help automate this process. You can also manually adjust the Laravel version in your `composer.json` file to test specific versions.