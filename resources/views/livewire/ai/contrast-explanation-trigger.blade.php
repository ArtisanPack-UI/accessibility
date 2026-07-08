<div class="a11y-ai-trigger a11y-ai-trigger--contrast">
    <div class="a11y-ai-trigger__row">
        <div>
            <label for="a11y-ai-fg" class="a11y-ai-trigger__label">{{ __('Foreground') }}</label>
            <input id="a11y-ai-fg" type="text" wire:model.defer="foreground" class="a11y-ai-trigger__input" placeholder="#333333"/>
            @error('foreground')
                <p class="a11y-ai-trigger__field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="a11y-ai-bg" class="a11y-ai-trigger__label">{{ __('Background') }}</label>
            <input id="a11y-ai-bg" type="text" wire:model.defer="background" class="a11y-ai-trigger__input" placeholder="#ffffff"/>
            @error('background')
                <p class="a11y-ai-trigger__field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="a11y-ai-ctx" class="a11y-ai-trigger__label">{{ __('Context') }}</label>
            <select id="a11y-ai-ctx" wire:model.defer="context" class="a11y-ai-trigger__input">
                <option value="body_text">{{ __('Body text (4.5:1)') }}</option>
                <option value="large_text">{{ __('Large text (3:1)') }}</option>
                <option value="ui">{{ __('UI component (3:1)') }}</option>
            </select>
        </div>
    </div>

    <button
        type="button"
        wire:click="explain"
        wire:loading.attr="disabled"
        wire:target="explain"
        class="a11y-ai-trigger__button"
    >
        <span wire:loading.remove wire:target="explain">{{ __('Explain contrast') }}</span>
        <span wire:loading wire:target="explain">{{ __('Explaining…') }}</span>
    </button>

    @if ($error !== '')
        <div class="a11y-ai-trigger__error" role="alert">{{ $error }}</div>
    @endif

    @if ($result !== null)
        <div class="a11y-ai-trigger__results" aria-label="{{ __('Contrast explanation') }}">
            <p class="a11y-ai-trigger__result-ratios">
                {{ __('Measured') }}: <strong>{{ number_format($result['current_ratio'], 2) }}:1</strong>
                / {{ __('Required') }}: <strong>{{ number_format($result['required_ratio'], 2) }}:1</strong>
            </p>
            <p class="a11y-ai-trigger__result-explanation">{{ $result['explanation'] }}</p>

            @if (! empty($result['suggested_alternatives']))
                <h4 class="a11y-ai-trigger__result-heading">{{ __('Suggested alternatives') }}</h4>
                <ul>
                    @foreach ($result['suggested_alternatives'] as $alt)
                        <li wire:key="alt-{{ $loop->index }}">
                            <span
                                class="a11y-ai-trigger__swatch"
                                style="background: {{ $alt['bg'] }}; color: {{ $alt['fg'] }};"
                            >Aa</span>
                            <code>{{ $alt['fg'] }}</code> / <code>{{ $alt['bg'] }}</code>
                            — {{ number_format($alt['ratio'], 2) }}:1
                            ({{ __('delta') }}: {{ number_format($alt['delta_from_original'], 2) }})
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
