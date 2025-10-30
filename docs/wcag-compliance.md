# WCAG Compliance

This document outlines the accessibility package's support for the Web Content Accessibility Guidelines (WCAG).

## WCAG 2.1 and 2.2 Support

The package has been upgraded to support WCAG 2.1 and 2.2 guidelines, including non-text contrast requirements, enhanced contrast ratios, and new accessibility criteria.

### Non-Text Contrast

WCAG 2.1 introduced a new requirement for non-text contrast. The `a11yCheckContrastColor` method now supports a `non-text` level, which checks for a minimum contrast ratio of 3:1.

```php
$a11y = new A11y();

// Check for non-text contrast
$a11y->a11yCheckContrastColor('#949494', '#FFFFFF', 'non-text'); // true
```

### Large Text vs. Normal Text

The contrast requirements for large text are different from those for normal text. The `a11yCheckContrastColor` method now includes a `$isLargeText` parameter to handle this distinction.

According to WCAG, large text is defined as text that is 18pt (24px) or larger, or 14pt (18.66px) or larger and bold.

```php
$a11y = new A11y();

// Check contrast for large text
$a11y->a11yCheckContrastColor('#8A8A8A', '#FFFFFF', 'AA', true); // true
```

### AAA Level Support

The package now supports the AAA level contrast requirements. You can use the `AAA` level in the `a11yCheckContrastColor` method to check for a contrast ratio of at least 7:1 for normal text and 4.5:1 for large text.

```php
$a11y = new A11y();

// Check for AAA level contrast
$a11y->a11yCheckContrastColor('#595959', '#FFFFFF', 'AAA'); // true
```

## Generating Accessible Colors

The `AccessibleColorGenerator` class can be used to generate accessible text colors for a given background color. The `generateAccessibleTextColor` method now supports generating colors for different WCAG levels.

```php
$colorGenerator = new AccessibleColorGenerator();

// Generate a color that meets AAA standards
$colorGenerator->generateAccessibleTextColor('#3b82f6', true, 'AAA');
```
