<?php

/**
 * Livewire trigger for the ColorContrastExplanationAgent.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Livewire\Ai;

use ArtisanPack\Accessibility\Ai\Agents\ColorContrastExplanationAgent;
use ArtisanPack\Accessibility\Livewire\Ai\Concerns\HandlesAgentErrors;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

/**
 * Livewire surface that turns a failing color pair into a plain-language
 * explanation and a set of accessible alternatives via
 * {@see ColorContrastExplanationAgent}.
 *
 *
 * @since      2.2.0
 */
class ContrastExplanationTrigger extends Component
{
    use HandlesAgentErrors;

    /**
     * @since 2.2.0
     */
    public string $foreground = '#777777';

    /**
     * @since 2.2.0
     */
    public string $background = '#ffffff';

    /**
     * WCAG usage context (`body_text`, `large_text`, `ui`).
     *
     * @since 2.2.0
     */
    public string $context = 'body_text';

    /**
     * Last successful output from the agent.
     *
     * @since 2.2.0
     *
     * @var array<string, mixed>|null
     */
    public ?array $result = null;

    /**
     * @since 2.2.0
     */
    public string $error = '';

    /**
     * @since 2.2.0
     */
    public function explain(): void
    {
        $this->result = null;
        $this->error = '';

        $this->validate([
            'foreground' => 'required|string',
            'background' => 'required|string',
            'context' => 'required|string|in:body_text,large_text,ui',
        ]);

        try {
            $this->result = ColorContrastExplanationAgent::for([
                'foreground' => $this->foreground,
                'background' => $this->background,
                'context' => $this->context,
            ])->run();
        } catch (Throwable $e) {
            $this->error = $this->errorMessageForAgentException($e, __('AI contrast explanation'));
        }
    }

    /**
     * @since 2.2.0
     */
    public function render(): View
    {
        return view('accessibility::livewire.ai.contrast-explanation-trigger');
    }
}
