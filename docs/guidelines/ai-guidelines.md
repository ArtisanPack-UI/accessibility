---
title: AI Guidelines
---

# AI Guidelines

This package provides AI guidelines for use with AI-powered development tools.

## Laravel Boost Integration

If you're using [Laravel Boost](https://github.com/laravel/boost), this package automatically provides AI guidelines for working with accessibility features.

### Automatic Setup

When you install this package and run:

```bash
php artisan boost:install
```

Laravel Boost will automatically load the AI guidelines from this package, giving your AI assistant complete knowledge of:

- Color contrast checking functions
- Accessible text color generation
- Tailwind CSS color support
- Helper functions and facade usage
- WCAG 2.0 compliance best practices

No manual configuration needed - the guidelines are loaded automatically!

### Manual Setup (Without Laravel Boost)

If you're not using Laravel Boost, you can still provide these guidelines to your AI assistant by copying the contents of `resources/boost/guidelines/core.blade.php` into your AI's context or configuration.
