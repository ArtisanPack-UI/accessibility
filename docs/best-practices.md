# Best Practices

This document provides best practices for using the ArtisanPack UI Accessibility package effectively and performantly.

## Performance Best Practices

While the package is designed to be as performant as possible, color calculations can be computationally intensive. Here are some tips to ensure your application remains fast.

### Understanding Caching

The package includes a simple in-memory caching mechanism for contrast ratio calculations. This means that if you calculate the contrast for the same pair of colors multiple times in a single request, the calculation will only be performed once.

-   **How it works**: The cache is a static array that persists for the duration of a single PHP request. It stores the results of contrast calculations.
-   **Limitations**: The cache is not shared between different requests or servers. It is cleared at the end of each request.

For more details, see the [Performance and Caching](performance.md) documentation.

### Server-Side vs. Client-Side

-   **Server-Side (PHP)**: Performing calculations in PHP is great for initial page loads and for components rendered on the server. This is the primary way this package is intended to be used.
-   **Client-Side (JavaScript)**: For highly dynamic interfaces where colors change frequently on the client-side (e.g., a theme customizer), it might be more efficient to perform contrast calculations in JavaScript. While this package does not provide a JavaScript version, you can find many libraries that do. The principles are the same.

### Pre-calculating Colors

For static themes or a limited set of user-selectable themes, the best approach is to pre-calculate all accessible colors and store them.

-   **Configuration File**: You can create a configuration file (e.g., `config/themes.php`) that stores all your theme colors and their accessible text colors. This avoids any runtime calculations.
-   **Build Process**: If you are using a build process (like with Vite or Mix), you could have a script that generates a CSS file with all your accessible color combinations.

### Efficient Usage in Loops

If you are rendering a list of items and need to perform color calculations for each item, be mindful of performance.

**DON'T**: Call the functions with the same parameters repeatedly inside a loop if you can avoid it.

```blade
@foreach ($items as $item)
    {{-- This will hit the cache, but it's still a function call in a loop --}}
    <div style="background-color: {{ $item->color }}; color: {{ a11yGetContrastColor($item->color) }};">
        {{ $item->name }}
    </div>
@endforeach
```

**DO**: If possible, calculate the colors in your controller or component and pass them to the view. Or, if the colors are repeated, calculate them once and store them in a variable.

```php
// In a component or controller
$itemsWithColors = $items->map(function ($item) {
    $item->textColor = a11yGetContrastColor($item->color);
    return $item;
});
```

## General Best Practices

### Beyond Automated Tools

This package is a powerful tool to help you build more accessible websites, but it is not a replacement for comprehensive accessibility testing.

-   **Manual Testing**: Always test your application manually. Use a keyboard to navigate, and use a screen reader to experience your site as a visually impaired user would.
-   **User Feedback**: Listen to feedback from users with disabilities. They are the experts on their own experience.

### Holistic Accessibility

Color contrast is just one aspect of web accessibility. Remember to consider other important areas:

-   **Keyboard Navigation**: All interactive elements should be focusable and operable with a keyboard.
-   **Screen Reader Support**: Use semantic HTML and ARIA attributes to ensure your content is understandable to screen reader users.
-   **Semantic HTML**: Use HTML elements for their intended purpose (e.g., `<button>` for buttons, `<nav>` for navigation).
