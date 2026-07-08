<?php

/**
 * Accessibility Service Provider
 *
 * Registers the accessibility services with the Laravel application.
 *
 * @since   1.0.0
 */

namespace ArtisanPack\Accessibility\Laravel;

use ArtisanPack\Accessibility\Ai\Agents\AriaSuggestionAgent;
use ArtisanPack\Accessibility\Ai\Agents\ColorContrastExplanationAgent;
use ArtisanPack\Accessibility\Ai\Agents\ContentAccessibilityAgent;
use ArtisanPack\Accessibility\Console\AuditColorsCommand;
use ArtisanPack\Accessibility\Console\GeneratePaletteCommand;
use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\Caching\CacheManager;
use ArtisanPack\Accessibility\Core\Contracts\Config;
use ArtisanPack\Accessibility\Core\Performance\BatchProcessor;
use ArtisanPack\Accessibility\Events\ColorContrastChecked;
use ArtisanPack\Accessibility\Listeners\LogColorContrastCheck;
use ArtisanPack\Accessibility\Livewire\Ai\AriaSuggestionTrigger;
use ArtisanPack\Accessibility\Livewire\Ai\ContentAnalysisTrigger;
use ArtisanPack\Accessibility\Livewire\Ai\ContrastExplanationTrigger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Service provider for the Accessibility package.
 *
 * This class registers the A11y service as a singleton in the Laravel
 * service container, making it available throughout the application.
 *
 * @since 1.0.0
 */
class A11yServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ColorContrastChecked::class => [
            LogColorContrastCheck::class,
        ],
    ];

    /**
     * Register the accessibility services.
     *
     * Binds the A11y class to the service container as a singleton
     * with the key 'a11y'.
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        $this->app->singleton(Config::class, LaravelConfig::class);

        $this->app->singleton('a11y', function ($app) {
            $config = $app->make(Config::class);
            $cacheConfig = $config->get('artisanpack.accessibility.cache');
            $cacheManager = new CacheManager($cacheConfig);
            $colorGenerator = new AccessibleColorGenerator(null, null, $cacheManager);
            $batchProcessor = new BatchProcessor($colorGenerator, $colorGenerator->getCache());

            return new A11y($config, null, $colorGenerator, $batchProcessor);
        });

        $this->mergeConfigFrom(
            __DIR__.'/../../config/accessibility.php', 'artisanpack-accessibility-temp'
        );

        $this->app->afterResolving('eloquent.factory', function ($factory) {
            $factory->load(__DIR__.'/../../database/factories');
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @since  1.0.0
     */
    public function boot(): void
    {
        $this->mergeConfiguration();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'accessibility');

        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__.'/../../config/accessibility.php' => config_path('artisanpack/accessibility.php'),
                ], 'artisanpack-package-config'
            );

            // Register CLI commands
            $this->commands([
                AuditColorsCommand::class,
                GeneratePaletteCommand::class,
            ]);
        }

        $this->validateConfig(config('artisanpack.accessibility'));

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }

        $this->registerAiLivewireComponents();
    }

    /**
     * AI features contributed by this package, discovered by
     * `artisanpack-ui/ai` at boot via its `aiFeatures()` convention.
     *
     * Each feature is toggle-able independently and no-ops when the
     * toggle is off — the trigger UIs shipped with this package all
     * surface the resulting `FeatureDisabledException` as a friendly
     * error message.
     *
     * @since 2.2.0
     *
     * @return array<string, array{agent: class-string, package: string, label: string, description: string}>
     */
    public function aiFeatures(): array
    {
        if (! class_exists(\ArtisanPackUI\Ai\Agents\ArtisanPackAgent::class)) {
            return [];
        }

        return [
            'a11y.content_analysis'     => [
                'agent'       => ContentAccessibilityAgent::class,
                'package'     => 'artisanpack-ui/accessibility',
                'label'       => 'Content accessibility analysis',
                'description' => 'Finds content-level accessibility issues (ambiguous link text, vague headings, undefined jargon) that static rules miss.',
            ],
            'a11y.aria_suggestion'      => [
                'agent'       => AriaSuggestionAgent::class,
                'package'     => 'artisanpack-ui/accessibility',
                'label'       => 'ARIA attribute suggestion',
                'description' => 'Given a custom component\'s markup and behavior, suggests appropriate ARIA roles, states, and properties.',
            ],
            'a11y.contrast_explanation' => [
                'agent'       => ColorContrastExplanationAgent::class,
                'package'     => 'artisanpack-ui/accessibility',
                'label'       => 'Contrast failure explanation',
                'description' => 'Explains in plain language why a color pair fails contrast and suggests alternatives that preserve brand intent.',
            ],
        ];
    }

    /**
     * Register the Livewire trigger surfaces if Livewire is installed.
     *
     * Skipped silently when `livewire/livewire` is not available so the
     * package still installs in non-Livewire apps that consume the
     * agents via the JSON endpoints or the React/Vue components.
     *
     * @since 2.2.0
     */
    protected function registerAiLivewireComponents(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        if (! class_exists(\ArtisanPackUI\Ai\Agents\ArtisanPackAgent::class)) {
            return;
        }

        \Livewire\Livewire::component('a11y-ai-content-analysis', ContentAnalysisTrigger::class);
        \Livewire\Livewire::component('a11y-ai-aria-suggestion', AriaSuggestionTrigger::class);
        \Livewire\Livewire::component('a11y-ai-contrast-explanation', ContrastExplanationTrigger::class);
    }

    /**
     * Merges the package's default configuration with the user's customizations.
     *
     * This method ensures that the user's settings in `config/artisanpack.php`
     * take precedence over the package's default values.
     *
     * @since 2.1.1
     */
    protected function mergeConfiguration(): void
    {
        $packageDefaults = config('artisanpack-accessibility-temp', []);
        $userConfig = config('artisanpack.accessibility', []);
        $mergedConfig = array_replace_recursive($packageDefaults, $userConfig);
        config(['artisanpack.accessibility' => $mergedConfig]);
    }

    protected function validateConfig($config): void
    {
        $validator = Validator::make(
            $config, [
                'wcag_thresholds.aa' => 'required|numeric|min:1|max:21',
                'wcag_thresholds.aaa' => 'required|numeric|min:1|max:21',
                'large_text_thresholds.font_size' => 'required|integer|min:1',
                'large_text_thresholds.font_weight' => 'required|string',
                'cache.default' => 'required|string|in:array,file,null',
                'cache.stores.array.limit' => 'required_if:cache.default,array|integer|min:0',
                'cache.stores.file.path' => 'required_if:cache.default,file|string',
            ]
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid accessibility configuration: '.$validator->errors()->first());
        }
    }
}
