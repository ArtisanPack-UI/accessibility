<?php

/**
 * Shared exception → JsonResponse mapping for the AI HTTP controllers.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Ai\Http\Concerns;

use ArtisanPackUI\Ai\Exceptions\FeatureDisabledException;
use ArtisanPackUI\Ai\Exceptions\FeatureError;
use ArtisanPackUI\Ai\Exceptions\MissingCredentialsException;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Runs an agent callable and maps the AI-package exception hierarchy to
 * a consistent JSON envelope so the three AI controllers do not each
 * copy-paste the same catch ladder.
 *
 * Prompter-originated FeatureErrors — raised by
 * `LaravelAiAgentPrompter` with the sentinel feature key `(prompter)` —
 * indicate a provider transport failure rather than a domain input
 * problem, so they map to 502 with a generic message that does not leak
 * provider identity or HTTP status codes to callers.
 *
 *
 * @since      2.2.0
 */
trait RespondsToAgentRun
{
    /**
     * Execute the agent callable and convert the outcome to a JSON
     * response using the shared envelope shape `{ data | error }`.
     *
     * @since 2.2.0
     *
     * @param  callable  $run  Callable returning the agent's structured output.
     * @param  string  $fallbackMessage  Message used for unexpected exceptions and reported provider transport failures.
     */
    protected function runAgent(callable $run, string $fallbackMessage): JsonResponse
    {
        try {
            $output = $run();

            return new JsonResponse(['data' => $output]);
        } catch (FeatureDisabledException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        } catch (MissingCredentialsException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 503);
        } catch (FeatureError $e) {
            if ($this->isPrompterError($e)) {
                report($e);

                return new JsonResponse(
                    ['error' => __('The AI provider is currently unavailable.')],
                    502,
                );
            }

            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return new JsonResponse(['error' => $fallbackMessage], 500);
        }
    }

    /**
     * Detect a FeatureError raised by the prompter (transport failure)
     * rather than a domain input error from the agent.
     *
     * `FeatureError::forFeature()` bakes the feature key into the
     * message via sprintf; the `(prompter)` sentinel is the AI
     * package's convention for transport-layer errors, so a substring
     * match on the formatted marker is stable across releases.
     *
     * @since 2.2.0
     *
     * @param  FeatureError  $error  Exception under inspection.
     */
    protected function isPrompterError(FeatureError $error): bool
    {
        return str_contains($error->getMessage(), 'AI feature "(prompter)"');
    }
}
