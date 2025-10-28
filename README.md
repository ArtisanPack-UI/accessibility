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

## Basic Usage

### Color Contrast Utilities

```php
// Check if text should be black or white on a background
$textColor = a11yCSSVarBlackOrWhite('#3b82f6'); // Returns 'black' or 'white'

// Get the hex code for the most accessible text color
$hexColor = a11yGetContrastColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'

// Check if two colors have sufficient contrast
$hasGoodContrast = a11yCheckContrastColor('#3b82f6', '#ffffff'); // Returns true or false
```

### Accessible Color Generator

```php
use ArtisanPackUI\Accessibility\AccessibleColorGenerator;

$generator = new AccessibleColorGenerator();

// Get black or white text color based on background
$textColor = $generator->generateAccessibleTextColor('#3b82f6');

// Generate a tinted/shaded version that's accessible
$tintedColor = $generator->generateAccessibleTextColor('#3b82f6', true);

// Using Tailwind color names
$textColor = $generator->generateAccessibleTextColor('blue-500');

// Using rgb() color strings
$textColor = $generator->generateAccessibleTextColor('rgb(59, 130, 246)');

// Using hsl() color strings
$textColor = $generator->generateAccessibleTextColor('hsl(217, 91%, 60%)');
```

### Helper Functions

```php
// Simple usage (black or white)
$textColor = generateAccessibleTextColor('#3b82f6');

// Tinted/shaded version
$tintedColor = generateAccessibleTextColor('#3b82f6', true);
```

### Laravel Facade

```php
use ArtisanPackUI\Accessibility\Facades\A11y;

$textColor = A11y::a11yCSSVarBlackOrWhite('#3b82f6');
$hexColor = A11y::a11yGetContrastColor('#3b82f6');
```

## Documentation

For complete documentation, visit the [Documentation Home](docs/home.md).

### Quick Links

**Guides**
- [Getting Started](docs/guides/getting-started.md) - Installation and setup instructions
- [Usage Guide](docs/guides/usage.md) - Detailed examples and practical implementations

**Reference**
- [API Reference](docs/reference/api-reference.md) - Complete technical documentation
- [Tailwind Colors Reference](docs/reference/tailwind-colors.md) - Supported color names and values

**Guidelines**
- [AI Guidelines](docs/guidelines/ai-guidelines.md) - Best practices for AI-assisted development

### Documentation Structure

The documentation is organized into three main sections:
- **[Guides](docs/guides.md)**: Step-by-step instructions and usage examples
- **[Reference](docs/reference.md)**: Technical documentation and API references  
- **[Guidelines](docs/guidelines.md)**: Best practices and development guidelines

## Requirements

- PHP 8.2 or higher
- Laravel 5.3 or higher (for Laravel integration)

## Contributing

As an open source project, this package is open to contributions from anyone. Please [read through the contributing
guidelines](CONTRIBUTING.md) to learn more about how you can contribute to this project.

## License

This package is open-sourced software licensed under the [GPL-3.0-or-later license](LICENSE).
