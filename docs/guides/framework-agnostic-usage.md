# Framework-Agnostic Usage

The ArtisanPack UI Accessibility package has been designed to be framework-agnostic, allowing you to use its core functionality in any PHP project, not just Laravel applications.

## Direct Instantiation

You can instantiate the `A11y` class directly in your project. It requires an implementation of the `ArtisanPack\Accessibility\Core\Contracts\Config` interface.

```php
use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Core\Config;

$config = new Config(['accessibility' => [
    'wcag_thresholds' => [
        'aa' => 4.5,
        'aaa' => 7.0,
    ],
    'large_text_thresholds' => [
        'font_size' => 18,
        'font_weight' => 'bold',
    ],
    'cache_size' => 1000,
]]);

$a11y = new A11y($config);

// Now you can use the methods
$textColor = $a11y->a11yGetContrastColor('#FF0000'); // returns '#FFFFFF'
```

The default `Config` class (`ArtisanPack\Accessibility\Core\Config`) uses the `illuminate/support` package for its `data_get` and `data_set` functions. If you want to use this class, you will need to add `illuminate/support` to your project's dependencies.

```bash
composer require illuminate/support
```

## Creating a Custom Config Implementation

If you don't want to use the default `Config` class, you can create your own implementation of the `ArtisanPack\Accessibility\Core\Contracts\Config` interface. This allows you to integrate the package with your framework's specific configuration system.

The interface requires you to implement three methods: `get`, `set`, and `has`.

```php
namespace App\MyProject;

use ArtisanPack\Accessibility\Core\Contracts\Config;

class MyConfig implements Config
{
    // Your implementation here
    public function get(string $key, mixed $default = null): mixed
    {
        // Your logic to get a config value
    }

    public function set(string $key, mixed $value): void
    {
        // Your logic to set a config value
    }

    public function has(string $key): bool
    {
        // Your logic to check if a config value exists
    }
}
```

## Example: Symfony Integration

Here is an example of how you could integrate the package into a Symfony application.

First, you would create a custom `Config` implementation that uses Symfony's `ParameterBag`.

```php
// src/Service/SymfonyConfig.php
namespace App\Service;

use ArtisanPack\Accessibility\Core\Contracts\Config;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SymfonyConfig implements Config
{
    public function __construct(private ParameterBagInterface $params)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->params->get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        $this->params->set($key, $value);
    }

    public function has(string $key): bool
    {
        return $this->params->has($key);
    }
}
```

Then, you would register the services in your `services.yaml`:

```yaml
# config/services.yaml
parameters:
    accessibility:
        wcag_thresholds:
            aa: 4.5
            aaa: 7.0
        large_text_thresholds:
            font_size: 18
            font_weight: 'bold'
        cache_size: 1000

services:
    _defaults:
        autowire: true
        autoconfigure: true

    ArtisanPack\Accessibility\Core\Contracts\Config:
        class: App\Service\SymfonyConfig

    ArtisanPack\Accessibility\Core\A11y:
        public: true
```

Now you can use the `A11y` service in your controllers or other services:

```php
// src/Controller/MyController.php
namespace App\Controller;

use ArtisanPack\Accessibility\Core\A11y;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MyController extends AbstractController
{
    public function myAction(A11y $a11y): Response
    {
        $textColor = $a11y->a11yGetContrastColor('#FF0000');
        // ...
    }
}
```
