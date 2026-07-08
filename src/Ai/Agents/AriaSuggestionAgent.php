<?php

/**
 * ARIA suggestion agent.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */

declare( strict_types=1 );

namespace ArtisanPack\Accessibility\Ai\Agents;

use ArtisanPackUI\Ai\Agents\ArtisanPackAgent;
use ArtisanPackUI\Ai\Contracts\AgentPrompter;
use ArtisanPackUI\Ai\Credentials\Credentials;
use ArtisanPackUI\Ai\Exceptions\FeatureError;

/**
 * Suggests ARIA roles, states, and properties for a custom component
 * described by its markup and behavior. Aimed at developer-facing
 * tooling (docs generators, component reviewers, package linters).
 *
 * ## Input
 *
 * ```
 * [
 *   'markup'      => string,   // required, HTML snippet
 *   'behavior'    => string,   // required, plain-language description
 *   'framework'   => string,   // optional, e.g. "livewire", "react", "vue"
 *   'existing_aria' => array<string, string>, // optional map of already-present attributes
 * ]
 * ```
 *
 * ## Output schema
 *
 * ```
 * {
 *   role:        ?string,           // suggested role, or null when native semantics cover it
 *   attributes:  [                  // ARIA states & properties to add
 *     { name: string, value: string, rationale: string }
 *   ],
 *   keyboard:    string[],          // human-readable keyboard interactions to implement
 *   notes:       string[]           // advisory notes (e.g. "already covered by native <button>")
 * }
 * ```
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */
class AriaSuggestionAgent extends ArtisanPackAgent
{
    /**
     * {@inheritDoc}
     */
    public string $featureKey = 'a11y.aria_suggestion';

    /**
     * {@inheritDoc}
     */
    public string $package = 'artisanpack-ui/accessibility';

    /**
     * {@inheritDoc}
     */
    public string $defaultModel = 'claude-sonnet-4-6';

    /**
     * {@inheritDoc}
     */
    public function instructions(): string
    {
        return <<<'PROMPT'
You are an accessibility engineer specializing in ARIA. Given a component's markup and a description of its behavior, suggest the minimal ARIA role, states, and properties needed to make it accessible to assistive technology.

Follow the "first rule of ARIA": if a native HTML element with the required semantics and behavior can be used, do NOT add a role. When native semantics already cover the component, return `role: null`, no attributes, and add a note explaining why.

For the attributes you do suggest:
- Prefer standard WAI-ARIA 1.2 roles and attributes.
- Include only attributes that are required by the pattern OR meaningfully improve assistive-tech output.
- Skip attributes already present in `existing_aria` unless the value is wrong.
- Give each attribute a one-line rationale tied to the observed behavior.

Also list the keyboard interactions the pattern requires (e.g. "Escape closes the dialog and returns focus to the trigger").

Return a JSON object with keys: role (string or null), attributes (array of {name, value, rationale}), keyboard (array of strings), notes (array of strings).
PROMPT;
    }

    /**
     * {@inheritDoc}
     */
    public function outputSchema(): array
    {
        return [
            'type'                 => 'object',
            'additionalProperties' => false,
            'required'             => [ 'role', 'attributes', 'keyboard', 'notes' ],
            'properties'           => [
                'role'       => [
                    'type' => [ 'string', 'null' ],
                ],
                'attributes' => [
                    'type'  => 'array',
                    'items' => [
                        'type'                 => 'object',
                        'additionalProperties' => false,
                        'required'             => [ 'name', 'value', 'rationale' ],
                        'properties'           => [
                            'name'      => [ 'type' => 'string' ],
                            'value'     => [ 'type' => 'string' ],
                            'rationale' => [ 'type' => 'string' ],
                        ],
                    ],
                ],
                'keyboard'   => [
                    'type'  => 'array',
                    'items' => [ 'type' => 'string' ],
                ],
                'notes'      => [
                    'type'  => 'array',
                    'items' => [ 'type' => 'string' ],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function execute( Credentials $credentials, string $model, string $instructions ): array
    {
        $normalized = $this->normalizeInput( $this->input() );

        $prompter = app( AgentPrompter::class );

        $result = $prompter->prompt(
            credentials: $credentials,
            model: $model,
            instructions: $instructions,
            message: $this->buildMessage( $normalized ),
            outputSchema: $this->outputSchema(),
        );

        return [
            'output'        => $this->validateOutput( $result['output'] ),
            'input_tokens'  => (int) ( $result['input_tokens'] ?? 0 ),
            'output_tokens' => (int) ( $result['output_tokens'] ?? 0 ),
        ];
    }

    /**
     * @since 2.2.0
     *
     * @param  mixed  $input Raw input.
     *
     * @return array{ markup: string, behavior: string, framework: string, existing_aria: array<string, string> }
     */
    protected function normalizeInput( mixed $input ): array
    {
        if ( ! is_array( $input ) ) {
            throw FeatureError::forFeature(
                $this->featureKey,
                'input must be an array with `markup` and `behavior` keys.',
            );
        }

        $markup   = isset( $input['markup'] ) && is_string( $input['markup'] ) ? trim( $input['markup'] ) : '';
        $behavior = isset( $input['behavior'] ) && is_string( $input['behavior'] ) ? trim( $input['behavior'] ) : '';

        if ( '' === $markup ) {
            throw FeatureError::forFeature( $this->featureKey, '`markup` must be a non-empty string.' );
        }

        if ( '' === $behavior ) {
            throw FeatureError::forFeature( $this->featureKey, '`behavior` must be a non-empty string.' );
        }

        $framework = isset( $input['framework'] ) && is_string( $input['framework'] ) ? trim( $input['framework'] ) : '';

        $existingAria = [];

        if ( isset( $input['existing_aria'] ) && is_array( $input['existing_aria'] ) ) {
            foreach ( $input['existing_aria'] as $name => $value ) {
                if ( is_string( $name ) && is_scalar( $value ) ) {
                    $existingAria[ $name ] = (string) $value;
                }
            }
        }

        return [
            'markup'        => $markup,
            'behavior'      => $behavior,
            'framework'     => $framework,
            'existing_aria' => $existingAria,
        ];
    }

    /**
     * @since 2.2.0
     *
     * @param  array{ markup: string, behavior: string, framework: string, existing_aria: array<string, string> }  $normalized Normalized input.
     *
     * @return array<int, array<string, string>>
     */
    protected function buildMessage( array $normalized ): array
    {
        $parts = [
            [ 'type' => 'text', 'text' => "Behavior:\n" . $normalized['behavior'] ],
            [ 'type' => 'text', 'text' => "Markup:\n" . $normalized['markup'] ],
        ];

        if ( '' !== $normalized['framework'] ) {
            $parts[] = [
                'type' => 'text',
                'text' => 'Framework: ' . $normalized['framework'],
            ];
        }

        if ( [] !== $normalized['existing_aria'] ) {
            $parts[] = [
                'type' => 'text',
                'text' => "Existing ARIA attributes:\n" . json_encode(
                    $normalized['existing_aria'],
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ),
            ];
        }

        return $parts;
    }

    /**
     * @since 2.2.0
     *
     * @param  array<string, mixed>  $output Decoded model output.
     *
     * @return array{ role: ?string, attributes: array<int, array{name: string, value: string, rationale: string}>, keyboard: array<int, string>, notes: array<int, string> }
     */
    protected function validateOutput( array $output ): array
    {
        $role = null;

        if ( array_key_exists( 'role', $output ) && is_string( $output['role'] ) && '' !== trim( $output['role'] ) ) {
            $role = trim( $output['role'] );
        }

        $attributes = [];

        if ( isset( $output['attributes'] ) && is_array( $output['attributes'] ) ) {
            foreach ( $output['attributes'] as $attribute ) {
                if ( ! is_array( $attribute ) ) {
                    continue;
                }

                $name      = isset( $attribute['name'] ) ? (string) $attribute['name'] : '';
                $value     = isset( $attribute['value'] ) ? (string) $attribute['value'] : '';
                $rationale = isset( $attribute['rationale'] ) ? (string) $attribute['rationale'] : '';

                if ( '' === $name ) {
                    continue;
                }

                $attributes[] = [
                    'name'      => $name,
                    'value'     => $value,
                    'rationale' => $rationale,
                ];
            }
        }

        return [
            'role'       => $role,
            'attributes' => $attributes,
            'keyboard'   => $this->stringList( $output['keyboard'] ?? [] ),
            'notes'      => $this->stringList( $output['notes'] ?? [] ),
        ];
    }

    /**
     * @since 2.2.0
     *
     * @param  mixed  $value Raw list.
     *
     * @return array<int, string>
     */
    protected function stringList( mixed $value ): array
    {
        if ( ! is_array( $value ) ) {
            return [];
        }

        $list = [];

        foreach ( $value as $item ) {
            if ( is_string( $item ) && '' !== trim( $item ) ) {
                $list[] = trim( $item );
            }
        }

        return $list;
    }
}
