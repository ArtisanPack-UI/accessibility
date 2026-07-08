<?php

/**
 * ARIA suggestion JSON endpoint.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */

declare( strict_types=1 );

namespace ArtisanPack\Accessibility\Ai\Http\Controllers;

use ArtisanPack\Accessibility\Ai\Agents\AriaSuggestionAgent;
use ArtisanPack\Accessibility\Ai\Http\Concerns\RespondsToAgentRun;
use ArtisanPack\Accessibility\Ai\Http\Requests\AriaSuggestionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Thin controller that adapts the {@see AriaSuggestionAgent} to a JSON
 * endpoint the React/Vue trigger surfaces call.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */
class AriaSuggestionController extends Controller
{
    use RespondsToAgentRun;

    /**
     * @since 2.2.0
     *
     * @param  AriaSuggestionRequest  $request  Validated request.
     *
     * @return JsonResponse
     */
    public function __invoke( AriaSuggestionRequest $request ): JsonResponse
    {
        $payload = $request->validated();

        return $this->runAgent(
            fn (): array => AriaSuggestionAgent::for( $payload )->run(),
            __( 'Failed to suggest ARIA attributes.' ),
        );
    }
}
