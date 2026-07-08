<?php

/**
 * Livewire trigger for the ContentAccessibilityAgent.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */

declare( strict_types=1 );

namespace ArtisanPack\Accessibility\Livewire\Ai;

use ArtisanPack\Accessibility\Ai\Agents\ContentAccessibilityAgent;
use ArtisanPack\Accessibility\Livewire\Ai\Concerns\HandlesAgentErrors;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

/**
 * Livewire surface that lets an author paste content, click "Analyze",
 * and see the {@see ContentAccessibilityAgent} findings inline.
 *
 * The component owns the transient form state; the actual model call
 * is delegated to the agent so the same feature toggle, credentials,
 * and caching apply whether the agent is invoked from Livewire, the
 * JSON API, or a downstream package.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */
class ContentAnalysisTrigger extends Component
{
    use HandlesAgentErrors;

    /**
     * Author-supplied content to analyze.
     *
     * @since 2.2.0
     *
     * @var string
     */
    public string $content = '';

    /**
     * Findings returned from the last successful run.
     *
     * @since 2.2.0
     *
     * @var array<int, array<string, string>>
     */
    public array $issues = [];

    /**
     * Message shown when a run fails.
     *
     * @since 2.2.0
     *
     * @var string
     */
    public string $error = '';

    /**
     * Whether the last run finished without any issues.
     *
     * @since 2.2.0
     *
     * @var bool
     */
    public bool $ran = false;

    /**
     * Run the agent.
     *
     * @since 2.2.0
     *
     * @return void
     */
    public function analyze(): void
    {
        $this->issues = [];
        $this->error  = '';
        $this->ran    = false;

        $this->validate( [ 'content' => 'required|string' ] );

        try {
            $output = ContentAccessibilityAgent::for( [ 'content' => $this->content ] )->run();

            $this->issues = $output['issues'] ?? [];
            $this->ran    = true;
        } catch ( Throwable $e ) {
            $this->error = $this->errorMessageForAgentException( $e, __( 'AI content analysis' ) );
        }
    }

    /**
     * @since 2.2.0
     *
     * @return View
     */
    public function render(): View
    {
        return view( 'accessibility::livewire.ai.content-analysis-trigger' );
    }
}
