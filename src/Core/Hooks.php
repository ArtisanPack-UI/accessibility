<?php

namespace ArtisanPack\Accessibility\Core;

use Throwable;

/**
 * Internal helper for firing `artisanpack-ui/hooks` filters from the
 * accessibility package without breaking framework-agnostic usage.
 *
 * The Facade dispatched by `applyFilters()` throws when no Laravel
 * application container is bound (e.g. when the package is used outside
 * of a Laravel app). This helper swallows that specific failure and
 * returns the caller's default, so contrast checks and color decisions
 * keep working in framework-agnostic mode.
 */
class Hooks
{
    public static function filter(string $hook, mixed $default, mixed ...$args): mixed
    {
        try {
            return applyFilters($hook, $default, ...$args);
        } catch (Throwable) {
            return $default;
        }
    }
}
