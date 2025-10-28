# Caching Implementation Plan for Color Calculations

## 1. Introduction

This document outlines the plan to implement a caching mechanism for the color calculation features in the ArtisanPack UI Accessibility package. The goal is to improve performance by caching the results of computationally intensive color contrast calculations.

## 2. Caching Strategy

We will implement a simple in-memory caching layer using a static array within the relevant classes. This approach is lightweight and avoids introducing external dependencies.

### 2.1. `A11y` Class Caching

We will add a static cache to the `A11y` class to store the results of `calculateContrastRatio`.

-   **Cache Storage:** A private static array `$contrastCache` will be added to the `A11y` class.
-   **Cache Key:** The cache key will be a concatenated string of the two hex color codes being compared, sorted alphabetically to ensure `(colorA, colorB)` and `(colorB, colorA)` produce the same key.
-   **Cache Logic:**
    1.  Inside `calculateContrastRatio`, a cache key will be generated from the input colors.
    2.  The method will first check if a result exists in `$contrastCache` for the given key.
    3.  If a cached result is found, it will be returned immediately.
    4.  If not, the contrast ratio will be calculated, and the result will be stored in the cache before being returned.

### 2.2. `AccessibleColorGenerator` Class Caching

Similarly, we will add a static cache to the `AccessibleColorGenerator` class for the `findClosestAccessibleShade` method.

-   **Cache Storage:** A private static array `$shadeCache` will be added.
-   **Cache Key:** The base hex color string will be used as the cache key.
-   **Cache Logic:**
    1.  At the beginning of `findClosestAccessibleShade`, the method will check for a cached result using the base hex color as the key.
    2.  If found, the cached accessible shade will be returned.
    3.  If not, the regular process of finding the closest shade will execute, and the result will be cached before being returned.

## 3. Cache Management

To prevent uncontrolled memory growth, we will implement a simple cache size limit.

-   **Cache Size Limit:** A constant `CACHE_SIZE_LIMIT` will be defined in the `src/Constants.php` class (e.g., set to 1000). This will allow for a single point of configuration.
-   **Cache Eviction:** When the cache size exceeds the limit, the oldest entry (the first element in the array) will be removed using `array_shift()`.

## 4. Monitoring

We will add static counters to track cache hits and misses for monitoring and testing purposes.

-   **Metrics:**
    -   `A11y::$cacheHits`
    -   `A11y::$cacheMisses`
    -   `AccessibleColorGenerator::$cacheHits`
    -   `AccessibleColorGenerator::$cacheMisses`
-   **Implementation:** These counters will be incremented within the caching logic. Public methods will be added to retrieve these metrics for testing.

## 5. Thread Safety

The use of static properties for caching in a web server context (like PHP-FPM) means the cache will be shared across all requests handled by a single PHP process. While this provides a performance benefit, it also requires consideration of concurrent access.

-   **Analysis:** For this specific use case, the risk of race conditions is low. The calculations are deterministic, so even if two requests calculate the same value simultaneously, they will arrive at the same result. The "last one wins" scenario for a cache write is acceptable.
-   **Conclusion:** No explicit locking mechanism will be implemented at this stage to keep the solution simple and avoid performance overhead. The behavior is acceptable for this application.

## 6. Testing

A new test file, `tests/Unit/CachingTest.php`, will be created to verify the caching functionality.

-   **Test Cases:**
    1.  **`test_contrast_ratio_is_cached`:**
        -   Call `a11yCheckContrastColor` multiple times with the same colors.
        -   Use the monitoring metrics to assert that the cache is hit on subsequent calls.
        -   Assert that the cache count does not exceed the defined limit.
    2.  **`test_find_closest_shade_is_cached`:**
        -   Call `findClosestAccessibleShade` multiple times with the same base color.
        -   Assert that the cache is hit on subsequent calls.
    3.  **`test_cache_eviction`:**
        -   Write a test to fill the cache beyond its limit and verify that the oldest items are evicted.

## 7. Documentation

A new documentation file, `docs/performance.md`, will be created.

-   **Content:**
    -   Explanation of the caching mechanism for both contrast ratio and shade finding.
    -   Details about the in-memory, per-process nature of the cache.
    -   Mention of the cache size limits and eviction strategy.
    -   Instructions on how to access cache metrics for debugging or monitoring (if applicable).

## 8. Files to be Modified/Created

-   **Modified:**
    -   `src/A11y.php`
    -   `src/AccessibleColorGenerator.php`
    -   `src/Constants.php`
-   **Created:**
    -   `tests/Unit/CachingTest.php`
    -   `docs/performance.md`
