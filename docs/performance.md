# Performance, Caching, and Benchmarking

To improve performance, the ArtisanPack UI Accessibility package includes a robust caching layer, a batch processor for bulk operations, and a performance monitoring system.

## Caching

The caching layer is managed by the `CacheManager` class, which supports multiple cache drivers. The results of expensive calculations, such as finding accessible shades, are cached to avoid redundant computations.

### Cache Drivers

The package supports the following cache drivers:

- **`array`**: A simple in-memory array cache. This is the default driver and is highly performant for a single request, but the cache is not persistent.
- **`file`**: A file-based cache that stores results in the filesystem. This driver provides persistent caching across requests. The cache path is configurable.
- **`null`**: A driver that does not cache anything. This is useful for development and testing.

### Configuration

The cache driver can be configured in the `config/accessibility.php` file:

```php
'cache' => [
    'default' => env('ACCESSIBILITY_CACHE_DRIVER', 'array'),

    'stores' => [
        'array' => [
            'driver' => 'array',
            'limit' => env('ACCESSIBILITY_CACHE_SIZE', 1000),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data/accessibility'),
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
],
```

## Batch Processing

The `BatchProcessor` class allows you to process multiple colors in a single operation, which is much more efficient than processing them one by one, especially when using a persistent cache driver.

### Usage

You can get an instance of the `BatchProcessor` from the `A11y` facade:

```php
use ArtisanPack\Accessibility\Facades\A11y;

$colors = ['#ff0000', '#00ff00', '#0000ff'];
$accessibleColors = A11y::batch()->generateAccessibleTextColors($colors);
```

The `BatchProcessor` will automatically use the configured cache driver and will read and write to the cache in bulk, which can significantly reduce I/O operations.

## Performance Monitoring

The package dispatches Laravel events to allow developers to monitor the performance of the accessibility calculations.

### Events

- `ArtisanPack\Accessibility\Core\Events\CacheHit`: Dispatched when a value is found in the cache.
- `ArtisanPack\Accessibility\Core\Events\CacheMiss`: Dispatched when a value is not found in the cache.
- `ArtisanPack\Accessibility\Core\Events\BatchProcessingCompleted`: Dispatched after a batch operation is completed. This event contains the total number of colors processed, the number of cache hits, and the total duration of the operation.

You can listen for these events in your application's `EventServiceProvider` to integrate with your own monitoring and logging solutions.

## Performance Benchmarking

This package includes performance benchmarks to monitor the efficiency of the color generation algorithms. These benchmarks are built using [PHPBench](https://phpbench.github.io/).

### Available Benchmarks

- **`benchBatchProcessor`**: Measures the performance of the `BatchProcessor` with different cache drivers.
- **`benchBatchProcessorWithTint`**: Measures the performance of the `BatchProcessor` with tinting enabled.

### Running Benchmarks Locally

To run the benchmarks locally, use the following command:

```bash
vendor/bin/phpbench run --report=default
```

### CI Integration

The benchmarks are run as part of the CI/CD pipeline in the `benchmark` stage. The results are available in the job artifacts. This helps to monitor the performance of the package over time and identify any regressions.
