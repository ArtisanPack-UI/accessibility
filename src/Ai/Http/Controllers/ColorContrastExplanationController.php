<?php

/**
 * Color-contrast explanation JSON endpoint.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Ai\Http\Controllers;

use ArtisanPack\Accessibility\Ai\Agents\ColorContrastExplanationAgent;
use ArtisanPack\Accessibility\Ai\Http\Concerns\RespondsToAgentRun;
use ArtisanPack\Accessibility\Ai\Http\Requests\ColorContrastExplanationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Thin controller that adapts the
 * {@see ColorContrastExplanationAgent} to a JSON endpoint.
 *
 *
 * @since      2.2.0
 */
class ColorContrastExplanationController extends Controller
{
    use RespondsToAgentRun;

    /**
     * @since 2.2.0
     *
     * @param  ColorContrastExplanationRequest  $request  Validated request.
     */
    public function __invoke(ColorContrastExplanationRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return $this->runAgent(
            fn (): array => ColorContrastExplanationAgent::for($payload)->run(),
            __('Failed to explain contrast failure.'),
        );
    }
}
