# Dynamic Theming

This document explains how to use the dynamic theming feature to generate light and dark themes for your application.

## Defining Base Colors

To generate a theme, you first need to define a set of base colors. These colors are defined as an associative array where the keys are the color names and the values are the hex color codes.

```php
$baseColors = [
    'primary' => '#3b82f6',
    'secondary' => '#6b7280',
    'accent' => '#f59e0b',
];
```

## Generating Themes

Once you have your base colors, you can use the `ThemeGenerator` class to generate the light and dark theme variants.

```php
use ArtisanPack\Accessibility\Core\Theming\ThemeGenerator;

$themeGenerator = new ThemeGenerator(/* ... */);

$themes = $themeGenerator->generate($baseColors);
```

The `$themes` variable will now contain an array with `light` and `dark` keys, each containing a theme with CSS custom properties.

## Using Themes

### CSS

You can export the theme to a CSS file using the `export` method.

```php
$css = $themeGenerator->export($themes, 'css');
file_put_contents('theme.css', $css);
```

This will generate a CSS file with the following structure:

```css
:root {
    --color-primary: #...;
    --color-secondary: #...;
}

@media (prefers-color-scheme: dark) {
    :root {
        --color-primary: #...;
        --color-secondary: #...;
    }
}
```

### CSS-in-JS

The generated theme array is compatible with most CSS-in-JS libraries. You can export the theme to JSON and then import it into your JavaScript.

```php
$json = $themeGenerator->export($themes, 'json');
file_put_contents('theme.json', $json);
```

### Real-time Theme Switching

You can use the generated themes to implement real-time theme switching in your application. By changing the class on the `<html>` or `<body>` element, you can switch between light and dark modes.

## API Reference

### `ThemeGenerator`

- `generate(array $baseColors): array`
- `generateFor(string $color, string $mode = 'light'): string`
- `export(array $theme, string $format = 'css'): string`

### `CssVariableParser`

- `parse(string $cssValue): ?string`
- `resolve(string $variableName, array $theme): ?string`

### `ThemeValidator`

- `validate(array $theme): bool`
