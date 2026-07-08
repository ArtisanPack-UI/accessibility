<?php

/**
 * Shared exception mapping for the AI Livewire trigger components.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */

declare( strict_types=1 );

namespace ArtisanPack\Accessibility\Livewire\Ai\Concerns;

use ArtisanPackUI\Ai\Exceptions\FeatureDisabledException;
use ArtisanPackUI\Ai\Exceptions\FeatureError;
use ArtisanPackUI\Ai\Exceptions\MissingCredentialsException;
use Throwable;

/**
 * Maps the AI-package exception hierarchy to a translated,
 * user-facing string so the three Livewire trigger components do not
 * each copy-paste the same catch ladder.
 *
 * Prompter-originated FeatureErrors (feature key `(prompter)`) leak
 * provider identity and HTTP status when surfaced verbatim, so they
 * collapse to a generic "provider unavailable" message and get
 * reported for observability instead of shown to the end user.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */
trait HandlesAgentErrors
{
    /**
     * Produce the translated error string for an agent exception.
     *
     * @since 2.2.0
     *
     * @param  Throwable  $error         Exception raised by the agent run.
     * @param  string     $featureLabel  Human-readable feature label (already translated).
     *
     * @return string
     */
    protected function errorMessageForAgentException( Throwable $error, string $featureLabel ): string
    {
        if ( $error instanceof FeatureDisabledException ) {
            return __( ':feature is disabled for this site.', [ 'feature' => $featureLabel ] );
        }

        if ( $error instanceof MissingCredentialsException ) {
            return __( 'AI credentials are not configured.' );
        }

        if ( $error instanceof FeatureError ) {
            if ( $this->isPrompterError( $error ) ) {
                report( $error );

                return __( 'The AI provider is currently unavailable.' );
            }

            return $error->getMessage();
        }

        report( $error );

        return __( ':feature failed. Please try again.', [ 'feature' => $featureLabel ] );
    }

    /**
     * Distinguish prompter (transport) errors from agent-domain errors.
     *
     * `FeatureError::forFeature()` bakes the feature key into the
     * message via sprintf; the AI package uses the `(prompter)`
     * sentinel for transport failures.
     *
     * @since 2.2.0
     *
     * @param  FeatureError  $error  Exception under inspection.
     *
     * @return bool
     */
    protected function isPrompterError( FeatureError $error ): bool
    {
        return str_contains( $error->getMessage(), 'AI feature "(prompter)"' );
    }
}
