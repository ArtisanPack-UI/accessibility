# Migration and Advanced Usage

This guide is for users who are familiar with the basic functions of the package and want to migrate to a more advanced, scalable, and maintainable architecture.

## From Basic Helpers to Advanced Architecture

When you first start using the package, it's common to sprinkle the helper functions (`a11yGetContrastColor()`, etc.) throughout your Blade views and components. While this is fine for smaller projects, it can become hard to manage as your application grows.

### `a11yGetContrastColor` vs. `generateAccessibleTextColor`

It's important to understand the difference between these two main functions:

-   `a11yGetContrastColor(string $backgroundColor)`: This function will always return either black (`#000000`) or white (`#FFFFFF`), whichever has a better contrast ratio against the background color.
-   `generateAccessibleTextColor(string $backgroundColor, bool $tint = false)`: This function is more powerful.
    -   If `$tint` is `false` (the default), it behaves similarly to `a11yGetContrastColor`.
    -   If `$tint` is `true`, it will attempt to find a lighter or darker shade of the *original background color* that is accessible against the background. This is great for creating more subtle and aesthetically pleasing color palettes.

**When to use which:**

-   Use `a11yGetContrastColor` when you need a simple, high-contrast text color (e.g., for body text on a colored background).
-   Use `generateAccessibleTextColor` with `$tint = true` when you want to create accessible color variations for UI elements like button borders or hover states.

## Building a Theming Service

As your application grows, you should centralize your theming logic into a dedicated service class. This makes your theme consistent, easy to manage, and simple to update.

Here is a step-by-step guide to creating a `ThemeService`:

**1. Create the Service Class:**

```php
// app/Services/ThemeService.php

namespace App\Services;

class ThemeService
{
    protected string $primaryColor;

    public function __construct(string $primaryColor = '#3b82f6')
    {
        $this->primaryColor = $primaryColor;
    }

    public function getThemeVariables(): array
    {
        return [
            '--primary-color' => $this->primaryColor,
            '--primary-text-color' => a11yGetContrastColor($this->primaryColor),
            '--primary-hover-color' => generateAccessibleTextColor($this->primaryColor, true),
            // ... add other colors
        ];
    }
}
```

**2. Create a Service Provider:**

```php
// app/Providers/ThemeServiceProvider.php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ThemeService::class, function ($app) {
            // Here, you could fetch the primary color from a database setting
            $primaryColor = setting('theme.primary_color', '#3b82f6');
            return new ThemeService($primaryColor);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $themeService = $this->app->make(ThemeService::class);
        View::share('themeVariables', $themeService->getThemeVariables());
    }
}
```

**3. Use it in your Layout:**

Now, in your main layout file, you can easily output the CSS variables.

```blade
// resources/views/layouts/app.blade.php

<style>
    :root {
        @foreach ($themeVariables as $key => $value)
            {{ $key }}: {{ $value }};
        @endforeach
    }
</style>
```

## Integrating with a Design System

If you are building a design system or a component library, you can use the accessibility package to enforce accessibility standards at the core of your system.

-   **Base Components**: In your base components (for buttons, alerts, etc.), use the package to ensure that any color combination is accessible by default.
-   **Theme Generation**: Use the package to build tools that allow designers or developers to generate new, fully accessible themes for your design system.
-   **Automated Testing**: You can write automated tests that use the package to check all the color combinations in your design system and fail the build if any of them do not meet accessibility standards.
