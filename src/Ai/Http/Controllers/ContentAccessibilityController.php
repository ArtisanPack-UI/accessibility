<?php

/**
 * Content accessibility JSON endpoint.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Ai\Http\Controllers;

use ArtisanPack\Accessibility\Ai\Agents\ContentAccessibilityAgent;
use ArtisanPack\Accessibility\Ai\Http\Concerns\RespondsToAgentRun;
use ArtisanPack\Accessibility\Ai\Http\Requests\ContentAccessibilityRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Thin controller that adapts the {@see ContentAccessibilityAgent} to
 * an HTTP surface consumable by React, Vue, or any other client that
 * prefers a JSON endpoint over an in-language SDK.
 *
 *
 * @since      2.2.0
 */
class ContentAccessibilityController extends Controller
{
    use RespondsToAgentRun;

    /**
     * Analyze the supplied content and return the agent's findings.
     *
     * @since 2.2.0
     *
     * @param  ContentAccessibilityRequest  $request  Validated request.
     */
    public function __invoke(ContentAccessibilityRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return $this->runAgent(
            fn (): array => ContentAccessibilityAgent::for($payload)->run(),
            __('Failed to analyze content.'),
        );
    }
}
