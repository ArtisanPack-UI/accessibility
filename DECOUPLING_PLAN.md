# Decoupling from Laravel Dependencies Plan

This document outlines the plan to refactor the ArtisanPack UI Accessibility package to be framework-agnostic.

## 1. Project Structure Changes

We will create two new directories in `src`: `Core` and `Laravel`.

*   `src/Core`: This directory will contain the framework-agnostic core logic of the package.
*   `src/Laravel`: This directory will contain the Laravel-specific integration code, such as the service provider and facade.

The following file moves will occur:

*   `src/A11y.php` -> `src/Core/A11y.php`
*   `src/WcagValidator.php` -> `src/Core/WcagValidator.php`
*   `src/AccessibleColorGenerator.php` -> `src/Core/AccessibleColorGenerator.php`
*   `src/Constants.php` -> `src/Core/Constants.php`
*   `src/helpers.php` -> `src/Core/helpers.php`
*   `src/Analysis/*` -> `src/Core/Analysis/*`
*   `src/A11yServiceProvider.php` -> `src/Laravel/A11yServiceProvider.php`
*   `src/Facades/A11y.php` -> `src/Laravel/Facades/A11y.php`

## 2. Refactoring Core Classes

The classes moved to `src/Core` will be refactored to remove all Laravel dependencies.

*   **`src/Core/A11y.php`**:
    *   The `getToastDuration()` method will be removed. This is a UI concern and is tightly coupled to Laravel's authentication. A different approach will be needed for framework-agnostic UI components.
    *   The `getFromConfig()` method will be updated to be more robust and not rely on Laravel-specific functions. We will introduce a new `Config` class that can be passed to the `A11y` constructor.

*   **`src/Core/WcagValidator.php`**: No changes are needed. This class is already framework-agnostic.

## 3. Laravel Integration Layer

The `src/Laravel` directory will contain the code that integrates the core logic with Laravel.

*   **`src/Laravel/A11yServiceProvider.php`**:
    *   The service provider will be updated to register the `A11y` class from the `Core` directory.
    *   It will pass a Laravel-specific `Config` implementation to the `A11y` constructor, which will use Laravel's `config()` helper.
    *   The `validateConfig` method will continue to use the `Validator` facade.

*   **`src/Laravel/Facades/A11y.php`**: The facade will be updated to point to the `A11y` class in the `Core` directory.

## 4. `composer.json` Changes

*   The `illuminate/support` dependency will be moved from `require` to `suggest`.
*   The `autoload` section will be updated to reflect the new directory structure.
*   The `extra.laravel` section will be updated to point to the new location of the service provider and facade.

## 5. Testing

*   A new test file, `tests/Unit/FrameworkAgnosticTest.php`, will be created to test the core functionality in a framework-agnostic way. This test will not use any Laravel-specific test helpers.
*   Existing tests will be updated to reflect the new directory structure and refactoring.

## 6. Documentation

The documentation will be updated to explain how to use the package in both Laravel and non-Laravel projects. This will include:

*   How to instantiate and use the `A11y` class directly.
*   How to create a custom `Config` implementation.
*   How to use the package with other frameworks (with examples for Symfony).

## 7. Backward Compatibility

Backward compatibility for Laravel users will be maintained. The service provider and facade will continue to work as they do now.

This plan will allow us to decouple the core logic of the package from Laravel, making it more reusable and extensible, while still providing a seamless experience for Laravel users.
