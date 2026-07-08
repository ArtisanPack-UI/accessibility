<?php

/**
 * Color-contrast explanation agent.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */

declare( strict_types=1 );

namespace ArtisanPack\Accessibility\Ai\Agents;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\WcagValidator;
use ArtisanPackUI\Ai\Agents\ArtisanPackAgent;
use ArtisanPackUI\Ai\Contracts\AgentPrompter;
use ArtisanPackUI\Ai\Credentials\Credentials;
use ArtisanPackUI\Ai\Exceptions\FeatureError;

/**
 * When a color pair fails contrast, produces a plain-language
 * explanation of *why* it fails and offers concrete alternative color
 * pairs that preserve as much of the original brand intent as possible.
 *
 * Contrast math is computed locally (deterministic, no model tokens
 * needed) so the model only reasons about the explanation and the
 * palette suggestions. The suggested alternatives are also re-checked
 * locally before returning so the caller never receives a suggestion
 * that itself fails.
 *
 * ## Input
 *
 * ```
 * [
 *   'foreground'    => string,             // hex or Tailwind color name
 *   'background'    => string,             // hex or Tailwind color name
 *   'context'       => 'body_text'|'large_text'|'ui', // WCAG usage
 *   'brand_palette' => string[]            // optional list of brand colors to prefer
 * ]
 * ```
 *
 * ## Output schema
 *
 * ```
 * {
 *   explanation:            string,
 *   current_ratio:          float,
 *   required_ratio:         float,
 *   suggested_alternatives: [
 *     { fg: string, bg: string, ratio: float, delta_from_original: float }
 *   ]
 * }
 * ```
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */
class ColorContrastExplanationAgent extends ArtisanPackAgent
{
    /**
     * {@inheritDoc}
     */
    public string $featureKey = 'a11y.contrast_explanation';

    /**
     * {@inheritDoc}
     */
    public string $package = 'artisanpack-ui/accessibility';

    /**
     * {@inheritDoc}
     */
    public string $defaultModel = 'claude-haiku-4-5';

    /**
     * {@inheritDoc}
     */
    public function instructions(): string
    {
        return <<<'PROMPT'
You explain color-contrast failures in plain language and propose adjusted color pairs that preserve brand intent.

You will receive:
- The foreground and background hex colors that were tested.
- The measured contrast ratio and the WCAG-required ratio for the usage context.
- Optionally, a brand palette the author would like to draw suggestions from.

Requirements:
- Write a two-to-three sentence explanation: name the specific reason contrast fails (e.g. "the foreground is only slightly darker than the background", "both colors share the same lightness"), and reference the ratio numbers. Avoid buzzwords; write for a designer who understands color but not WCAG jargon.
- Propose 2-3 alternative pairs. Each alternative:
  - MUST clearly pass the required ratio.
  - SHOULD preserve as much of the original hue as possible — prefer adjusting lightness/saturation over swapping hue.
  - When a brand palette is provided, prefer colors from it or shades that stay within the palette's hue family.
  - `delta_from_original` is your own qualitative rating from 0.0 (nearly identical) to 1.0 (very different) summarising how far from the original pair the suggestion is.

Return a JSON object with keys: explanation (string), suggested_alternatives (array of {fg, bg, delta_from_original}). Do NOT return current_ratio or required_ratio — those are computed by the caller.

Each alternative object MUST use lowercase hex codes with a leading '#', six characters (e.g. "#1a2b3c").
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
            'required'             => [ 'explanation', 'suggested_alternatives' ],
            'properties'           => [
                'explanation'            => [ 'type' => 'string' ],
                'suggested_alternatives' => [
                    'type'  => 'array',
                    'items' => [
                        'type'                 => 'object',
                        'additionalProperties' => false,
                        'required'             => [ 'fg', 'bg', 'delta_from_original' ],
                        'properties'           => [
                            'fg'                  => [ 'type' => 'string' ],
                            'bg'                  => [ 'type' => 'string' ],
                            'delta_from_original' => [
                                'type'    => 'number',
                                'minimum' => 0,
                                'maximum' => 1,
                            ],
                        ],
                    ],
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

        $required = $this->requiredRatio( $normalized['context'] );
        $current  = $this->measureRatio( $normalized['foreground'], $normalized['background'] );

        $prompter = app( AgentPrompter::class );

        $result = $prompter->prompt(
            credentials: $credentials,
            model: $model,
            instructions: $instructions,
            message: $this->buildMessage( $normalized, $current, $required ),
            outputSchema: $this->outputSchema(),
        );

        return [
            'output'        => $this->validateOutput( $result['output'], $current, $required ),
            'input_tokens'  => (int) ( $result['input_tokens'] ?? 0 ),
            'output_tokens' => (int) ( $result['output_tokens'] ?? 0 ),
        ];
    }

    /**
     * @since 2.2.0
     *
     * @param  mixed  $input Raw input.
     *
     * @return array{ foreground: string, background: string, context: string, brand_palette: array<int, string> }
     */
    protected function normalizeInput( mixed $input ): array
    {
        if ( ! is_array( $input ) ) {
            throw FeatureError::forFeature(
                $this->featureKey,
                'input must be an array with `foreground`, `background`, and `context` keys.',
            );
        }

        $foreground = isset( $input['foreground'] ) && is_string( $input['foreground'] ) ? trim( $input['foreground'] ) : '';
        $background = isset( $input['background'] ) && is_string( $input['background'] ) ? trim( $input['background'] ) : '';
        $context    = isset( $input['context'] ) && is_string( $input['context'] ) ? trim( $input['context'] ) : 'body_text';

        if ( '' === $foreground || '' === $background ) {
            throw FeatureError::forFeature(
                $this->featureKey,
                '`foreground` and `background` must be non-empty color values.',
            );
        }

        $foregroundHex = $this->resolveToHex( $foreground );
        $backgroundHex = $this->resolveToHex( $background );

        if ( null === $foregroundHex || null === $backgroundHex ) {
            throw FeatureError::forFeature(
                $this->featureKey,
                sprintf(
                    '`foreground` (%s) and `background` (%s) must be hex codes or recognised Tailwind color names.',
                    $foreground,
                    $background,
                ),
            );
        }

        $foreground = $foregroundHex;
        $background = $backgroundHex;

        if ( ! in_array( $context, [ 'body_text', 'large_text', 'ui' ], true ) ) {
            throw FeatureError::forFeature(
                $this->featureKey,
                sprintf( 'unsupported context "%s"; expected one of body_text, large_text, ui.', $context ),
            );
        }

        $palette = [];

        if ( isset( $input['brand_palette'] ) && is_array( $input['brand_palette'] ) ) {
            foreach ( $input['brand_palette'] as $color ) {
                if ( is_string( $color ) && '' !== trim( $color ) ) {
                    $palette[] = trim( $color );
                }
            }
        }

        return [
            'foreground'    => $foreground,
            'background'    => $background,
            'context'       => $context,
            'brand_palette' => $palette,
        ];
    }

    /**
     * @since 2.2.0
     *
     * @param  array{ foreground: string, background: string, context: string, brand_palette: array<int, string> }  $normalized Normalized input.
     * @param  float                                                                                                  $current    Measured ratio.
     * @param  float                                                                                                  $required   Required ratio.
     *
     * @return array<int, array<string, string>>
     */
    protected function buildMessage( array $normalized, float $current, float $required ): array
    {
        $parts = [
            [
                'type' => 'text',
                'text' => sprintf(
                    "Foreground: %s\nBackground: %s\nContext: %s\nMeasured contrast ratio: %.2f\nRequired ratio: %.2f",
                    $normalized['foreground'],
                    $normalized['background'],
                    $normalized['context'],
                    $current,
                    $required,
                ),
            ],
        ];

        if ( [] !== $normalized['brand_palette'] ) {
            $parts[] = [
                'type' => 'text',
                'text' => 'Brand palette (preferred sources for suggestions): ' . implode( ', ', $normalized['brand_palette'] ),
            ];
        }

        return $parts;
    }

    /**
     * @since 2.2.0
     *
     * @param  array<string, mixed>  $output   Decoded model output.
     * @param  float                 $current  Measured ratio.
     * @param  float                 $required Required ratio.
     *
     * @return array{ explanation: string, current_ratio: float, required_ratio: float, suggested_alternatives: array<int, array{fg: string, bg: string, ratio: float, delta_from_original: float}> }
     */
    protected function validateOutput( array $output, float $current, float $required ): array
    {
        $explanation = isset( $output['explanation'] ) ? (string) $output['explanation'] : '';

        $alternatives = [];

        if ( isset( $output['suggested_alternatives'] ) && is_array( $output['suggested_alternatives'] ) ) {
            foreach ( $output['suggested_alternatives'] as $alt ) {
                if ( ! is_array( $alt ) ) {
                    continue;
                }

                $fg = isset( $alt['fg'] ) ? $this->resolveToHex( (string) $alt['fg'] ) : null;
                $bg = isset( $alt['bg'] ) ? $this->resolveToHex( (string) $alt['bg'] ) : null;

                if ( null === $fg || null === $bg ) {
                    continue;
                }

                $ratio = $this->measureRatio( $fg, $bg );

                if ( $ratio < $required ) {
                    continue;
                }

                $delta = isset( $alt['delta_from_original'] ) ? (float) $alt['delta_from_original'] : 0.5;
                $delta = max( 0.0, min( 1.0, $delta ) );

                $alternatives[] = [
                    'fg'                  => $fg,
                    'bg'                  => $bg,
                    'ratio'               => round( $ratio, 2 ),
                    'delta_from_original' => $delta,
                ];
            }
        }

        return [
            'explanation'            => $explanation,
            'current_ratio'          => round( $current, 2 ),
            'required_ratio'         => $required,
            'suggested_alternatives' => $alternatives,
        ];
    }

    /**
     * WCAG required ratio for the given usage context.
     *
     * @since 2.2.0
     *
     * @param  string  $context Usage context.
     *
     * @return float
     */
    protected function requiredRatio( string $context ): float
    {
        return match ( $context ) {
            'large_text' => 3.0,
            'ui'         => 3.0,
            default      => 4.5,
        };
    }

    /**
     * Compute the WCAG relative-luminance contrast ratio for two hex colors.
     *
     * Inputs are already normalised to 6-char hex by normalizeInput() and
     * validateOutput() before reaching this method.
     *
     * @since 2.2.0
     *
     * @param  string  $foreground Foreground hex.
     * @param  string  $background Background hex.
     *
     * @return float
     */
    protected function measureRatio( string $foreground, string $background ): float
    {
        return (float) app( WcagValidator::class )->calculateContrastRatio( $foreground, $background );
    }

    /**
     * Resolve a hex code or Tailwind name to a canonical 6-char lowercase hex.
     *
     * Returns null when the input cannot be resolved so callers can throw a
     * targeted FeatureError instead of silently producing garbage ratios.
     *
     * @since 2.2.0
     *
     * @param  string  $value Raw color string.
     *
     * @return string|null
     */
    protected function resolveToHex( string $value ): ?string
    {
        $resolved = app( AccessibleColorGenerator::class )->getHexFromColorString( $value );

        if ( null === $resolved ) {
            return null;
        }

        $hex = ltrim( strtolower( $resolved ), '#' );

        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return 1 === preg_match( '/^[0-9a-f]{6}$/', $hex ) ? '#' . $hex : null;
    }
}
