<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

use ArtisanPack\Accessibility\Ai\Agents\AriaSuggestionAgent;
use ArtisanPack\Accessibility\Ai\Agents\ColorContrastExplanationAgent;
use ArtisanPack\Accessibility\Ai\Agents\ContentAccessibilityAgent;
use ArtisanPack\Accessibility\Livewire\Ai\AriaSuggestionTrigger;
use ArtisanPack\Accessibility\Livewire\Ai\ContentAnalysisTrigger;
use ArtisanPack\Accessibility\Livewire\Ai\ContrastExplanationTrigger;
use ArtisanPackUI\Ai\Contracts\AgentPrompter;
use ArtisanPackUI\Ai\Contracts\CredentialResolver;
use ArtisanPackUI\Ai\Contracts\FeatureRegistry;
use ArtisanPackUI\Ai\Credentials\Credentials;
use ArtisanPackUI\Ai\Exceptions\FeatureError;
use Livewire\Livewire;

/**
 * A stub prompter reused across the surface tests below. Duplicated from
 * AiAgentsTest so each file can run in isolation without depending on
 * the other's helper definitions.
 */
class SurfaceStubPrompter implements AgentPrompter
{
    public array $last = [];

    public array $response;

    public function __construct(array $response)
    {
        $this->response = [
            'output' => $response,
            'input_tokens' => 5,
            'output_tokens' => 8,
        ];
    }

    public function prompt(
        Credentials $credentials,
        string $model,
        string $instructions,
        string|array $message,
        array $outputSchema,
    ): array {
        $this->last = compact('credentials', 'model', 'instructions', 'message', 'outputSchema');

        return $this->response;
    }
}

function surfaceBindCredentials(): void
{
    $resolver = Mockery::mock(CredentialResolver::class);
    $resolver->shouldReceive('forFeature')->andReturn(
        new Credentials(provider: 'anthropic', apiKey: 'sk-test', defaultModel: null),
    );

    app()->instance(CredentialResolver::class, $resolver);
}

function surfaceEnableFeature(string $key): void
{
    $registry = app(FeatureRegistry::class);

    if ($registry->get($key) === null) {
        $registry->register($key, match ($key) {
            'a11y.content_analysis' => ContentAccessibilityAgent::class,
            'a11y.aria_suggestion' => AriaSuggestionAgent::class,
            'a11y.contrast_explanation' => ColorContrastExplanationAgent::class,
        }, ['package' => 'artisanpack-ui/accessibility']);
    }

    $registry->enable($key);
    config()->set('artisanpack.ai.cache.enabled', false);
}

beforeEach(function (): void {
    surfaceBindCredentials();
});

/*
|--------------------------------------------------------------------------
| Service-provider integration
|--------------------------------------------------------------------------
*/

it('registers all three AI features when the AI package is loaded', function (): void {
    $registry = app(FeatureRegistry::class);

    expect($registry->get('a11y.content_analysis'))->not->toBeNull()
        ->and($registry->get('a11y.aria_suggestion'))->not->toBeNull()
        ->and($registry->get('a11y.contrast_explanation'))->not->toBeNull();
});

it('registers all three Livewire trigger components', function (): void {
    // Livewire::mount() will throw ComponentNotFoundException if the
    // registration on A11yServiceProvider::boot() did not run.
    Livewire::test(ContentAnalysisTrigger::class);
    Livewire::test(AriaSuggestionTrigger::class);
    Livewire::test(ContrastExplanationTrigger::class);
})->throwsNoExceptions();

/*
|--------------------------------------------------------------------------
| Livewire trigger components
|--------------------------------------------------------------------------
*/

it('populates issues on the content-analysis trigger and clears the error', function (): void {
    surfaceEnableFeature('a11y.content_analysis');
    app()->instance(AgentPrompter::class, new SurfaceStubPrompter([
        'issues' => [
            [
                'location' => 'link[0]',
                'issue_type' => 'ambiguous-link-text',
                'severity' => 'warning',
                'suggested_fix' => 'Rewrite "click here".',
            ],
        ],
    ]));

    Livewire::test(ContentAnalysisTrigger::class)
        ->set('content', 'Click here for details.')
        ->call('analyze')
        ->assertSet('error', '')
        ->assertSet('ran', true)
        ->assertCount('issues', 1);
});

it('surfaces the friendly message when credentials are missing', function (): void {
    surfaceEnableFeature('a11y.content_analysis');
    app()->forgetInstance(CredentialResolver::class);
    $resolver = Mockery::mock(CredentialResolver::class);
    $resolver->shouldReceive('forFeature')->andReturn(null);
    app()->instance(CredentialResolver::class, $resolver);

    Livewire::test(ContentAnalysisTrigger::class)
        ->set('content', 'anything')
        ->call('analyze')
        ->assertSet('error', 'AI credentials are not configured.')
        ->assertSet('ran', false);
});

it('collapses prompter-transport FeatureErrors to a generic message', function (): void {
    surfaceEnableFeature('a11y.aria_suggestion');
    $throwing = new class implements AgentPrompter
    {
        public function prompt(
            Credentials $credentials,
            string $model,
            string $instructions,
            string|array $message,
            array $outputSchema,
        ): array {
            throw FeatureError::forFeature('(prompter)', 'provider call failed: 401 Unauthorized');
        }
    };
    app()->instance(AgentPrompter::class, $throwing);

    Livewire::test(AriaSuggestionTrigger::class)
        ->set('markup', '<div></div>')
        ->set('behavior', 'it does things')
        ->call('suggest')
        ->assertSet('error', 'The AI provider is currently unavailable.');
});

/*
|--------------------------------------------------------------------------
| HTTP controllers
|--------------------------------------------------------------------------
*/

it('returns 200 with data from the content-analysis endpoint', function (): void {
    surfaceEnableFeature('a11y.content_analysis');
    app()->instance(AgentPrompter::class, new SurfaceStubPrompter([
        'issues' => [
            [
                'location' => 'heading[0]',
                'issue_type' => 'vague-heading',
                'severity' => 'info',
                'suggested_fix' => 'Rename it.',
            ],
        ],
    ]));

    $this->withoutMiddleware()
        ->postJson('/api/v1/a11y/ai/content-analysis', ['content' => 'A single sentence.'])
        ->assertSuccessful()
        ->assertJsonPath('data.issues.0.issue_type', 'vague-heading');
});

it('returns 422 with the FeatureError message on domain-input errors', function (): void {
    surfaceEnableFeature('a11y.contrast_explanation');
    app()->instance(AgentPrompter::class, new SurfaceStubPrompter([
        'explanation' => 'n/a',
        'suggested_alternatives' => [],
    ]));

    $this->withoutMiddleware()
        ->postJson('/api/v1/a11y/ai/contrast-explanation', [
            'foreground' => '#000000',
            'background' => '#ffffff',
            'context' => 'display_text',
        ])
        ->assertStatus(422);
});

it('rejects unknown color inputs with a 422 domain error', function (): void {
    surfaceEnableFeature('a11y.contrast_explanation');
    app()->instance(AgentPrompter::class, new SurfaceStubPrompter([
        'explanation' => 'n/a',
        'suggested_alternatives' => [],
    ]));

    $this->withoutMiddleware()
        ->postJson('/api/v1/a11y/ai/contrast-explanation', [
            'foreground' => 'not-a-real-color',
            'background' => '#ffffff',
        ])
        ->assertStatus(422);
});
