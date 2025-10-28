# Performance and Caching

To improve performance, the ArtisanPack UI Accessibility package includes a simple in-memory caching mechanism for computationally intensive color calculations.

## How it Works

The caching is implemented as a static array within the `A11y` and `AccessibleColorGenerator` classes. This means that the cache is shared across all requests handled by a single PHP process, which can provide a significant performance boost in a typical web server environment.

### Contrast Ratio Caching

The results of `calculateContrastRatio` in the `A11y` class are cached. The cache key is a concatenated string of the two hex color codes being compared, sorted alphabetically to ensure that the order of the colors does not matter.

### Accessible Shade Caching

The results of `findClosestAccessibleShade` in the `AccessibleColorGenerator` class are also cached. The base hex color string is used as the cache key.

## Cache Management

To prevent uncontrolled memory growth, the cache size is limited by the `CACHE_SIZE_LIMIT` constant defined in the `Constants` class. When the cache size exceeds this limit, the oldest entry is removed.

## Monitoring

For debugging and monitoring purposes, the following static properties are available:

-   `A11y::$cacheHits`
-   `A11y::$cacheMisses`
-   `AccessibleColorGenerator::$cacheHits`
-   `AccessibleColorGenerator::$cacheMisses`

These properties can be accessed to get insights into the cache performance.
