# Getting Started with ArtisanPack UI Accessibility

This guide will help you get started with the ArtisanPack UI Accessibility package, which provides tools for ensuring your web applications meet accessibility standards, particularly for color contrast.

## Requirements

- PHP 8.2 or higher
- Laravel 5.3 or higher (for Laravel integration)

## Installation

You can install the package via Composer:

```bash
composer require artisanpack-ui/accessibility
```

## Laravel Integration

### Service Provider Registration

If you're using Laravel 5.5 or higher with package auto-discovery, the service provider will be automatically registered.

For Laravel 5.3 or 5.4, you need to manually add the service provider to your `config/app.php` file:

```php
'providers' => [
    // Other service providers...
    ArtisanPackUI\Accessibility\A11yServiceProvider::class,
],
```

### Facade Registration

If you want to use the A11y facade, you can add it to the aliases array in your `config/app.php` file:

```php
'aliases' => [
    // Other aliases...
    'A11y' => ArtisanPackUI\Accessibility\Facades\A11y::class,
],
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

- Check out the [Usage Guide](usage.md) for more detailed examples
- See the [API Reference](api-reference.md) for a complete list of available methods
- Learn about [Tailwind Color Support](tailwind-colors.md) for using Tailwind CSS color names