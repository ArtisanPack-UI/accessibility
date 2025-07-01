# Usage Guide for ArtisanPack UI Accessibility

This guide provides detailed examples of how to use the ArtisanPack UI Accessibility package in your projects.

## Color Contrast Utilities

### Determining Text Color Based on Background

One of the most common accessibility challenges is ensuring text has sufficient contrast against its background. The package provides several methods to help with this:

#### Black or White Text

To determine whether black or white text would be more accessible on a given background color:

```php
// Using helper function
$textColorName = a11yCSSVarBlackOrWhite('#3b82f6'); // Returns 'black' or 'white'

// Using A11y class
$a11y = new ArtisanPackUI\Accessibility\A11y();
$textColorName = $a11y->a11yCSSVarBlackOrWhite('#3b82f6');

// Using Laravel facade
$textColorName = A11y::a11yCSSVarBlackOrWhite('#3b82f6');
```

This is particularly useful for CSS variable assignments:

```php
// In your PHP code that generates CSS variables
$cssVars = [
    '--text-color': a11yCSSVarBlackOrWhite('#3b82f6')
];
```

#### Getting Hex Color Code

If you need the actual hex color code instead of 'black' or 'white':

```php
// Using helper function
$hexColor = a11yGetContrastColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'

// Using A11y class
$a11y = new ArtisanPackUI\Accessibility\A11y();
$hexColor = $a11y->a11yGetContrastColor('#3b82f6');

// Using Laravel facade
$hexColor = A11y::a11yGetContrastColor('#3b82f6');
```

### Checking Contrast Ratio

To check if two colors have sufficient contrast according to WCAG guidelines (minimum 4.5:1 ratio):

```php
// Using helper function
$hasGoodContrast = a11yCheckContrastColor('#3b82f6', '#ffffff'); // Returns true or false

// Using A11y class
$a11y = new ArtisanPackUI\Accessibility\A11y();
$hasGoodContrast = $a11y->a11yCheckContrastColor('#3b82f6', '#ffffff');

// Using Laravel facade
$hasGoodContrast = A11y::a11yCheckContrastColor('#3b82f6', '#ffffff');
```

## Accessible Color Generator

The `AccessibleColorGenerator` class provides more advanced color generation capabilities:

### Basic Usage

```php
use ArtisanPackUI\Accessibility\AccessibleColorGenerator;

$generator = new AccessibleColorGenerator();

// Get black or white text color based on background
$textColor = $generator->generateAccessibleTextColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'
```

### Generating Tinted/Shaded Colors

Instead of just black or white, you can generate a tinted or shaded version of the original color that still meets accessibility standards:

```php
use ArtisanPackUI\Accessibility\AccessibleColorGenerator;

$generator = new AccessibleColorGenerator();

// Get a tinted/shaded version of the color that's accessible
$tintedColor = $generator->generateAccessibleTextColor('#3b82f6', true);
```

This is useful for creating more aesthetically pleasing color combinations while still maintaining accessibility.

### Using Tailwind Color Names

The `AccessibleColorGenerator` supports Tailwind CSS color names:

```php
use ArtisanPackUI\Accessibility\AccessibleColorGenerator;

$generator = new AccessibleColorGenerator();

// Using Tailwind color names
$textColor = $generator->generateAccessibleTextColor('blue-500'); // Same as using '#3b82f6'
$tintedColor = $generator->generateAccessibleTextColor('red-700', true);
```

See the [Tailwind Colors Reference](tailwind-colors.md) for a complete list of supported color names.

### Helper Function

For convenience, a global helper function is provided:

```php
// Simple usage (black or white)
$textColor = generateAccessibleTextColor('#3b82f6');

// Tinted/shaded version
$tintedColor = generateAccessibleTextColor('#3b82f6', true);

// Using Tailwind color names
$textColor = generateAccessibleTextColor('blue-500');
```

## User Accessibility Settings

### Toast Duration

The package provides a method to get the user's preferred toast notification duration:

```php
// Using helper function
$durationInMs = getToastDuration(); // Returns duration in milliseconds

// Using A11y class
$a11y = new ArtisanPackUI\Accessibility\A11y();
$durationInMs = $a11y->getToastDuration();

// Using Laravel facade
$durationInMs = A11y::getToastDuration();
```

This retrieves the user's setting or falls back to a default of 5 seconds (5000ms).

## Practical Examples

### Dynamic Button Styling

```php
$buttonBgColor = '#3b82f6'; // Blue background
$buttonTextColor = a11yGetContrastColor($buttonBgColor);

echo "<button style='background-color: {$buttonBgColor}; color: {$buttonTextColor};'>
      Accessible Button
      </button>";
```

### Theme Color Generation

```php
use ArtisanPackUI\Accessibility\AccessibleColorGenerator;

$generator = new AccessibleColorGenerator();
$primaryColor = '#3b82f6';

$theme = [
    'primary' => $primaryColor,
    'primary-text' => $generator->generateAccessibleTextColor($primaryColor),
    'primary-accent' => $generator->generateAccessibleTextColor($primaryColor, true),
];

// Use $theme array in your application
```

### Checking User-Generated Content

```php
$userBgColor = $_POST['background_color'] ?? '#ffffff';
$userTextColor = $_POST['text_color'] ?? '#000000';

if (!a11yCheckContrastColor($userBgColor, $userTextColor)) {
    $suggestedTextColor = a11yGetContrastColor($userBgColor);
    echo "Warning: Your selected colors don't have sufficient contrast. 
          We suggest using {$suggestedTextColor} for better readability.";
}
```