<?php

/**
 * Livewire trigger for the AriaSuggestionAgent.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Livewire\Ai;

use ArtisanPack\Accessibility\Ai\Agents\AriaSuggestionAgent;
use ArtisanPack\Accessibility\Livewire\Ai\Concerns\HandlesAgentErrors;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

/**
 * Livewire surface that turns a markup + behavior description into a
 * concrete ARIA recommendation via {@see AriaSuggestionAgent}. Aimed at
 * developer-facing tooling (docs generators, component reviewers).
 *
 *
 * @since      2.2.0
 */
class AriaSuggestionTrigger extends Component
{
    use HandlesAgentErrors;

    /**
     * @since 2.2.0
     */
    public string $markup = '';

    /**
     * @since 2.2.0
     */
    public string $behavior = '';

    /**
     * @since 2.2.0
     */
    public string $framework = '';

    /**
     * Last successful agent output; keyed by `role`, `attributes`, etc.
     *
     * @since 2.2.0
     *
     * @var array<string, mixed>|null
     */
    public ?array $suggestion = null;

    /**
     * @since 2.2.0
     */
    public string $error = '';

    /**
     * @since 2.2.0
     */
    public function suggest(): void
    {
        $this->suggestion = null;
        $this->error = '';

        $this->validate([
            'markup' => 'required|string',
            'behavior' => 'required|string',
            'framework' => 'nullable|string',
        ]);

        try {
            $this->suggestion = AriaSuggestionAgent::for([
                'markup' => $this->markup,
                'behavior' => $this->behavior,
                'framework' => $this->framework,
            ])->run();
        } catch (Throwable $e) {
            $this->error = $this->errorMessageForAgentException($e, __('ARIA suggestion'));
        }
    }

    /**
     * @since 2.2.0
     */
    public function render(): View
    {
        return view('accessibility::livewire.ai.aria-suggestion-trigger');
    }
}
