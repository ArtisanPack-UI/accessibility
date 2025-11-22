# Troubleshooting

This document lists common issues you might encounter while using the ArtisanPack UI Accessibility package and how to resolve them.

## Common Issues

### Invalid Color Formats

The functions in this package expect color strings in specific formats (hex, rgb, hsl, or Tailwind color names). If you pass an invalid format, the behavior can vary.

-   **Symptom**: A function might return a default value (like `_#FFFFFF_`) or throw an `InvalidArgumentException`.
-   **Solution**: Ensure that the color strings you pass to the package's functions are in a supported format. When dealing with user input, always validate the color format before passing it to the package.

```php
// Good: Validate user input
use Illuminate\Support\Facades\Validator;

$validator = Validator::make($request->all(), [
    'color' => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
]);

if ($validator->fails()) {
    // Handle invalid color format
}

$color = $request->input('color');
$textColor = a11yGetContrastColor($color);
```

### Caching Problems

The package uses an in-memory cache to improve performance. This cache is cleared at the end of each request. However, in long-running processes or unique server setups, you might encounter stale data (though it is unlikely in a typical web request lifecycle).

-   **Symptom**: The package returns an unexpected or outdated color value.
-   **Solution**: You can manually clear the cache using the `A11y::clearCache()` method. This will reset the cache for the current process.

```php
use ArtisanPackUI\Accessibility\Facades\A11y;

A11y::clearCache();
```

### Dependency Conflicts

-   **Symptom**: You get a Composer error when trying to install or update the package.
-   **Solution**: This package requires PHP 8.2+ and Laravel 5.3+. Ensure your environment meets these requirements. If you have a conflict with another package, you may need to investigate which dependency is causing the issue and see if it can be updated.
