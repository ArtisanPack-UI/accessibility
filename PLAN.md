# Configuration System Implementation Plan

## 1. Create Configuration File

- **File:** `config/accessibility.php`
- **Action:** Create a new configuration file to store default accessibility parameters.
- **Contents:**
    ```php
    <?php

    return [
        /*
        |--------------------------------------------------------------------------
        | WCAG Contrast Thresholds
        |--------------------------------------------------------------------------
        |
        | The contrast ratio thresholds for WCAG compliance.
        |
        */
        'wcag_thresholds' => [
            'aa' => 4.5,
            'aaa' => 7.0,
        ],

        /*
        |--------------------------------------------------------------------------
        | Large Text Thresholds
        |--------------------------------------------------------------------------
        |
        | The font size and weight that qualifies as "large text" according
        | to WCAG guidelines. Large text requires a lower contrast ratio.
        |
        */
        'large_text_thresholds' => [
            'font_size' => 18, // points
            'font_weight' => 'bold',
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Size
        |--------------------------------------------------------------------------
        |
        | The maximum number of items to store in the contrast cache.
        |
        */
        'cache_size' => 1000,
    ];
    ```

## 2. Update Service Provider

- **File:** `src/A11yServiceProvider.php`
- **Action:** Merge the package's default configuration with the user's published configuration.
- **Implementation:**
    ```php
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/accessibility.php', 'accessibility'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/accessibility.php' => config_path('accessibility.php'),
        ]);
    }
    ```

## 3. Refactor Core Logic

- **File:** `src/A11y.php`
- **Actions:**
    - Remove the dependency on `src/Constants.php`.
    - Update methods to use the new configuration values.
- **Implementation Details:**
    - In `a11yCheckContrastColor`, replace `Constants::WCAG_CONTRAST_AA` with `config('accessibility.wcag_thresholds.aa')`.
    - Create a new method `isLargeText(int $fontSize, string $fontWeight)` to check if text meets the large text criteria from the config.
    - Update `a11yCheckContrastColor` to accept optional `$fontSize` and `$fontWeight` parameters. If the text is large, use the `aa_large` threshold.
    - Update `a11yGetContrastColor` to use `config('accessibility.wcag_thresholds.aa')`.
    - Update the cache size limit to use `config('accessibility.cache_size')`.

## 4. Create Tests

- **File:** `tests/Unit/ConfigurationTest.php`
- **Actions:**
    - Create a new test file to verify the configuration system.
- **Test Cases:**
    - Test that default configuration values are loaded correctly.
    - Test that users can override default values by publishing and modifying the configuration file.
    - Test that environment variables can override configuration values.
    - Test that the validation logic prevents invalid configuration values.

## 5. Add Validation

- **File:** `src/A11yServiceProvider.php`
- **Action:** Add validation for the configuration values within the `register` method.
- **Implementation:**
    - Use Laravel's validator to check the types and ranges of the configuration values.
    - Throw a detailed exception if validation fails.

## 6. Create Documentation

- **File:** `docs/configuration.md`
- **Action:** Create a new documentation file explaining the configuration options.
- **Contents:**
    - Detail each configuration key (`wcag_thresholds`, `large_text_thresholds`, `cache_size`).
    - Provide examples of how to publish and customize the configuration.
    - Explain how to use environment variables for overrides (e.g., `WCAG_THRESHOLD_AA=5.0`).

## 7. Cleanup

- **File:** `src/Constants.php`
- **Action:** Delete the `Constants.php` file after all its values have been replaced by the new configuration system.
