# Real-World Examples

This document provides practical, real-world usage examples for the ArtisanPack UI Accessibility package. These examples are designed to help you integrate the package into your Laravel projects and build accessible UI components.

## Laravel Blade Component Examples

Here are some examples of how you can use the accessibility package to create accessible Blade components.

### Buttons

A common use case is to create buttons with dynamic background colors that need to have accessible text. You can use the `a11yGetContrastColor()` helper to determine whether the text should be light or dark.

```php
// app/View/Components/Button.php

namespace App\View\Components;

use Illuminate\View\Component;

class Button extends Component
{
    public string $bgColor;
    public string $textColor;

    public function __construct(string $bgColor = '#3b82f6')
    {
        $this->bgColor = $bgColor;
        $this->textColor = a11yGetContrastColor($this->bgColor);
    }

    public function render()
    {
        return view('components.button');
    }
}
```

```blade
// resources/views/components/button.blade.php

@props(['bgColor', 'textColor'])

<button {{ $attributes->merge(['class' => 'px-4 py-2 rounded']) }} style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    {{ $slot }}
</button>
```

**Usage:**

```blade
<x-button>Click Me</x-button>
<x-button bg-color="#dc2626">Delete</x-button>
```

### Alerts

Similar to buttons, alerts often have different background colors to indicate their severity.

```php
// app/View/Components/Alert.php

namespace App\View\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    public string $bgColor;
    public string $textColor;

    public function __construct(string $type = 'info')
    {
        $this->bgColor = match ($type) {
            'info' => '#3b82f6',
            'success' => '#16a34a',
            'warning' => '#f59e0b',
            'danger' => '#dc2626',
            default => '#6b7280',
        };
        $this->textColor = a11yGetContrastColor($this->bgColor);
    }

    public function render()
    {
        return view('components.alert');
    }
}
```

```blade
// resources/views/components/alert.blade.php

@props(['bgColor', 'textColor'])

<div {{ $attributes->merge(['class' => 'p-4 rounded']) }} style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    {{ $slot }}
</div>
```

**Usage:**

```blade
<x-alert type="success">Your profile has been updated.</x-alert>
<x-alert type="danger">There was an error with your submission.</x-alert>
```

## Livewire Component Integration

The accessibility package works great with Livewire for creating dynamic and interactive components.

### Dynamic Color Picker

This Livewire component allows a user to pick a color, and it will dynamically show an accessible text color on top of it.

```php
// app/Http/Livewire/ColorPicker.php

namespace App\Http\Livewire;

use Livewire\Component;

class ColorPicker extends Component
{
    public string $selectedColor = '#3b82f6';
    public string $accessibleTextColor;

    public function mount()
    {
        $this->updateTextColor();
    }

    public function updatedSelectedColor()
    {
        $this->updateTextColor();
    }

    private function updateTextColor()
    {
        $this->accessibleTextColor = a11yGetContrastColor($this->selectedColor);
    }

    public function render()
    {
        return view('livewire.color-picker');
    }
}
```

```blade
// resources/views/livewire/color-picker.blade.php

<div>
    <input type="color" wire:model.live="selectedColor">

    <div class="mt-4 p-8 rounded" style="background-color: {{ $selectedColor }};">
        <p style="color: {{ $accessibleTextColor }}; font-size: 1.5rem;">
            This text is accessible!
        </p>
    </div>
</div>
```

## Dynamic Theming

You can use the package to generate a full palette of accessible colors and export them as CSS variables for sitewide use.

### Generating a Theme with CSS Variables

In a service provider or a dedicated class, you can generate your color palette.

```php
// app/Providers/ThemeServiceProvider.php

use Illuminate\Support\Facades\View;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $primaryColor = '#3b82f6'; // This could come from a database setting

        $theme = [
            '--primary-color' => $primaryColor,
            '--primary-text-color' => a11yGetContrastColor($primaryColor),
            // ... generate other colors
        ];

        View::share('theme', $theme);
    }
}
```

Then, in your main layout file, you can output these as CSS variables.

```blade
// resources/views/layouts/app.blade.php

<style>
    :root {
        @foreach ($theme as $key => $value)
            {{ $key }}: {{ $value }};
        @endforeach
    }
</style>

<body style="background-color: var(--primary-color); color: var(--primary-text-color);">
    ...
</body>
```
