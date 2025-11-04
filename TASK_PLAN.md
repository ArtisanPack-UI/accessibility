
# Task Plan: Add CSS Custom Properties and Dynamic Theme Support

This document outlines the plan to implement support for CSS custom properties (CSS variables) and dynamic theme generation for modern web applications with dark/light mode support.

## 1. Foundational Theming Framework

### 1.1. Create `src/Theming` Directory
- Create a new directory `src/Theming` to house the new theming classes.

### 1.2. Create `src/Theming/CssVariableParser.php`
- **Purpose:** To parse CSS `var()` syntax and resolve custom property values.
- **Methods:**
    - `parse(string $cssValue): ?string`: Parses a CSS value. If it's a `var()`, it extracts the variable name (e.g., `--color-primary`).
    - `resolve(string $variableName, array $theme): ?string`: Resolves a CSS variable name against a given theme array.

### 1.3. Create `src/Theming/ThemeGenerator.php`
- **Purpose:** To generate light and dark theme variants from a base set of colors.
- **Dependencies:** `AccessibleColorGenerator`, `CssVariableParser`.
- **Methods:**
    - `__construct(AccessibleColorGenerator $colorGenerator, CssVariableParser $parser)`
    - `generate(array $baseColors): array`: Generates a full theme with `light` and `dark` variants.
        - For each color in `$baseColors`, it will generate an accessible contrasting color for both light and dark modes.
        - It will return a multi-dimensional array, e.g., `['light' => ['--color-primary' => '#...', '--color-text' => '#...'], 'dark' => [...]]`.
    - `generateFor(string $color, string $mode = 'light'): string`: Generates a single accessible color for a given base color and mode.

## 2. Integration with Existing Code

### 2.1. Update `src/Core/AccessibleColorGenerator.php`
- **Purpose:** To integrate the new theming capabilities.
- **Changes:**
    - Add a new method `fromTheme(string $cssValue, array $theme, string $mode = 'light'): string`.
    - This method will use `CssVariableParser` to identify and resolve CSS variables within the context of a theme.
    - If the value is not a CSS variable, it will be treated as a regular color string.

## 3. Testing

### 3.1. Create `tests/Unit/Theming` Directory
- Create a new directory `tests/Unit/Theming` for the new theming tests.

### 3.2. Create `tests/Unit/Theming/CssVariableParserTest.php`
- **Purpose:** To test the `CssVariableParser` class.
- **Tests:**
    - Test that it correctly parses `var()` syntax.
    - Test that it correctly extracts the variable name.
    - Test that it returns `null` for non-var values.
    - Test variable resolution against a theme array.

### 3.3. Create `tests/Unit/Theming/ThemeGeneratorTest.php`
- **Purpose:** To test the `ThemeGenerator` class.
- **Tests:**
    - Test that it generates both `light` and `dark` theme variants.
    - Test that the generated colors are accessible.
    - Test the `generateFor` method.
    - Mock the `AccessibleColorGenerator` to ensure it's being called correctly.

### 3.4. Update `tests/Unit/AccessibleColorGeneratorTest.php`
- Add tests for the new `fromTheme` method.

## 4. Advanced Features & Compliance

### 4.1. CSS-in-JS Support
- The `ThemeGenerator` will output nested arrays which are compatible with most CSS-in-JS libraries. The documentation will include examples for popular libraries.

### 4.2. Theme Validation
- Create a `ThemeValidator` class in `src/Theming`.
- **Methods:**
    - `validate(array $theme): bool`: Checks if a theme meets all accessibility requirements (e.g., contrast ratios).
    - This will be integrated into `ThemeGenerator` to ensure generated themes are compliant.

### 4.3. Theme Export Utilities
- Add an `export` method to `ThemeGenerator`.
- **Methods:**
    - `export(array $theme, string $format = 'css'): string`: Exports a theme to a specified format.
        - `css`: Exports as a CSS file with custom properties.
        - `json`: Exports as a JSON file.

## 5. Documentation

### 5.1. Create `docs/dynamic-theming.md`
- **Purpose:** To document the new dynamic theming feature.
- **Contents:**
    - How to define base colors.
    - How to generate themes.
    - How to use the themes in CSS, CSS-in-JS, and with real-time theme switching.
    - Examples for light and dark mode implementation.
    - API reference for the new classes.

## 6. Implementation Steps

1.  Create the directory structure (`src/Theming`, `tests/Unit/Theming`).
2.  Implement `CssVariableParser` and its tests.
3.  Implement `ThemeGenerator` and its tests.
4.  Update `AccessibleColorGenerator` and its tests.
5.  Implement `ThemeValidator` and integrate it into `ThemeGenerator`.
6.  Implement the `export` method in `ThemeGenerator`.
7.  Write the `docs/dynamic-theming.md` documentation.
8.  Run all tests to ensure everything is working correctly.
9.  Run `vendor/bin/pint --dirty` to format the code.
