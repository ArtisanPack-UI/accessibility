# Configuration

The accessibility package comes with a configuration file that allows you to customize the behavior of the accessibility checks.

## Publishing the Configuration

To publish the configuration file, run the following command:

```bash
php artisan vendor:publish --provider="ArtisanPackUI\Accessibility\A11yServiceProvider" --tag="config"
```

This will create a `config/artisanpack/accessibility.php` file in your application.

## Configuration Options

### WCAG Contrast Thresholds

These values define the minimum contrast ratios for WCAG compliance.

- `wcag_thresholds.aa`: The minimum contrast ratio for WCAG AA compliance (default: `4.5`).
- `wcag_thresholds.aaa`: The minimum contrast ratio for WCAG AAA compliance (default: `7.0`).

### Large Text Thresholds

These values define what qualifies as "large text" according to WCAG guidelines. Large text requires a lower contrast ratio.

- `large_text_thresholds.font_size`: The minimum font size in points for large text (default: `18`).
- `large_text_thresholds.font_weight`: The minimum font weight for large text (default: `'bold'`).

### Cache Size

This value determines the maximum number of items to store in the contrast cache.

- `cache_size`: The maximum number of items in the cache (default: `1000`).

## Environment Variable Overrides

You can also override the configuration values using environment variables in your `.env` file. The environment variable names should follow the pattern `ACCESSIBILITY_<CONFIG_KEY>`. For nested keys, use double underscores.

For example, to override the WCAG AA threshold, you would add the following to your `.env` file:

```
ACCESSIBILITY_WCAG_THRESHOLDS__AA=5.0
```
