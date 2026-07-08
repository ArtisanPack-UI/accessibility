<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

use ArtisanPack\Accessibility\Ai\Agents\AriaSuggestionAgent;
use ArtisanPack\Accessibility\Ai\Agents\ColorContrastExplanationAgent;
use ArtisanPack\Accessibility\Ai\Agents\ContentAccessibilityAgent;
use ArtisanPackUI\Ai\Contracts\AgentPrompter;
use ArtisanPackUI\Ai\Contracts\CredentialResolver;
use ArtisanPackUI\Ai\Contracts\FeatureRegistry;
use ArtisanPackUI\Ai\Credentials\Credentials;
use ArtisanPackUI\Ai\Exceptions\FeatureError;

/**
 * A stub prompter that captures the last call and returns a fixture
 * response, so we can exercise the agents without hitting a real LLM.
 */
class StubPrompter implements AgentPrompter
{
    public array $last = [];

    /** @var array<string, mixed> */
    public array $response;

    public function __construct(array $response)
    {
        $this->response = [
            'output' => $response,
            'input_tokens' => 10,
            'output_tokens' => 20,
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

function bindCredentials(): void
{
    $resolver = Mockery::mock(CredentialResolver::class);
    $resolver->shouldReceive('forFeature')->andReturn(
        new Credentials(provider: 'anthropic', apiKey: 'sk-test', defaultModel: null)
    );

    app()->instance(CredentialResolver::class, $resolver);
}

function enableFeature(string $key): void
{
    $registry = app(FeatureRegistry::class);

    if ($registry->get($key) === null) {
        // Not auto-discovered in this Testbench context; register manually.
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
    bindCredentials();
});

it('runs the content accessibility agent and normalizes issues', function (): void {
    enableFeature('a11y.content_analysis');

    $prompter = new StubPrompter([
        'issues' => [
            [
                'location' => 'link[0]',
                'issue_type' => 'ambiguous-link-text',
                'severity' => 'warning',
                'suggested_fix' => 'Replace "click here" with a descriptive label.',
            ],
            [
                'location' => '',
                'issue_type' => 'ignored',
                'severity' => 'warning',
                'suggested_fix' => 'This one should be dropped by validation.',
            ],
            [
                'location' => 'paragraph[3]',
                'issue_type' => 'undefined-jargon',
                'severity' => 'not-a-real-severity',
                'suggested_fix' => 'Define "OKR" on first use.',
            ],
        ],
    ]);

    app()->instance(AgentPrompter::class, $prompter);

    $output = ContentAccessibilityAgent::for([
        'content' => 'Click here to read more about our OKRs.',
        'structure' => [
            'links' => [
                ['href' => '/blog', 'text' => 'click here'],
            ],
        ],
    ])->run();

    expect($output['issues'])->toHaveCount(2);
    expect($output['issues'][0]['issue_type'])->toBe('ambiguous-link-text');
    expect($output['issues'][1]['severity'])->toBe('warning'); // coerced from unknown value
});

it('rejects content analysis with empty content', function (): void {
    enableFeature('a11y.content_analysis');
    app()->instance(AgentPrompter::class, new StubPrompter(['issues' => []]));

    ContentAccessibilityAgent::for(['content' => '  '])->run();
})->throws(FeatureError::class);

it('runs the aria suggestion agent and strips malformed attributes', function (): void {
    enableFeature('a11y.aria_suggestion');

    $prompter = new StubPrompter([
        'role' => 'switch',
        'attributes' => [
            ['name' => 'aria-checked', 'value' => 'false', 'rationale' => 'Reflects current state.'],
            ['name' => '', 'value' => 'x', 'rationale' => 'no name, should be dropped'],
            'not an array',
        ],
        'keyboard' => ['Space toggles the switch', ''],
        'notes' => ['Prefer <button> when the state is transient'],
    ]);

    app()->instance(AgentPrompter::class, $prompter);

    $output = AriaSuggestionAgent::for([
        'markup' => '<div class="toggle" tabindex="0"></div>',
        'behavior' => 'A rectangle the user can click or press Space to flip on and off.',
        'framework' => 'livewire',
    ])->run();

    expect($output['role'])->toBe('switch');
    expect($output['attributes'])->toHaveCount(1);
    expect($output['attributes'][0]['name'])->toBe('aria-checked');
    expect($output['keyboard'])->toEqual(['Space toggles the switch']);
});

it('preserves null role when native semantics cover the component', function (): void {
    enableFeature('a11y.aria_suggestion');

    app()->instance(AgentPrompter::class, new StubPrompter([
        'role' => null,
        'attributes' => [],
        'keyboard' => [],
        'notes' => ['Use a native <button> — no ARIA needed.'],
    ]));

    $output = AriaSuggestionAgent::for([
        'markup' => '<button>Save</button>',
        'behavior' => 'A button that submits the form.',
    ])->run();

    expect($output['role'])->toBeNull();
    expect($output['notes'])->toContain('Use a native <button> — no ARIA needed.');
});

it('discards contrast alternatives that still fail the required ratio', function (): void {
    enableFeature('a11y.contrast_explanation');

    // The model tries to slip a bad suggestion past; the agent must reject
    // it because the ratio for grey-on-grey is nowhere near 4.5:1.
    app()->instance(AgentPrompter::class, new StubPrompter([
        'explanation' => 'The two greys are almost the same lightness so almost no light difference remains between them.',
        'suggested_alternatives' => [
            ['fg' => '#000000', 'bg' => '#ffffff', 'delta_from_original' => 0.3],
            ['fg' => '#888888', 'bg' => '#999999', 'delta_from_original' => 0.1], // fails - kept out
            ['fg' => 'not-a-hex', 'bg' => '#ffffff', 'delta_from_original' => 0.2], // invalid - kept out
        ],
    ]));

    $output = ColorContrastExplanationAgent::for([
        'foreground' => '#888888',
        'background' => '#999999',
        'context' => 'body_text',
    ])->run();

    expect($output['suggested_alternatives'])->toHaveCount(1);
    expect($output['suggested_alternatives'][0]['fg'])->toBe('#000000');
    expect($output['required_ratio'])->toBe(4.5);
    expect($output['current_ratio'])->toBeGreaterThan(0.0);
});

it('rejects an unsupported contrast context', function (): void {
    enableFeature('a11y.contrast_explanation');
    app()->instance(AgentPrompter::class, new StubPrompter([
        'explanation' => 'n/a',
        'suggested_alternatives' => [],
    ]));

    ColorContrastExplanationAgent::for([
        'foreground' => '#000000',
        'background' => '#ffffff',
        'context' => 'display_text',
    ])->run();
})->throws(FeatureError::class);
