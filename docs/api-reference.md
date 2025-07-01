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
$a11y = new ArtisanPackUI\Accessibility\A11y();
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

#### `getToastDuration(): float|int`

Retrieves the user's preference for toast notification duration from their settings. If no setting is found, defaults to 5 seconds. The value is returned in milliseconds.

**Returns:**
- (float|int): The toast duration in milliseconds.

**Example:**
```php
$a11y = new ArtisanPackUI\Accessibility\A11y();
$durationInMs = $a11y->getToastDuration(); // Returns duration in milliseconds
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

### `getToastDuration(): float|int`

Retrieves the user's preference for toast notification duration from their settings. If no setting is found, defaults to 5 seconds. The value is returned in milliseconds.

**Returns:**
- (float|int): The toast duration in milliseconds.

**Example:**
```php
$durationInMs = getToastDuration(); // Returns duration in milliseconds
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