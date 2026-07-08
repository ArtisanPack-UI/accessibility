<div class="a11y-ai-trigger a11y-ai-trigger--aria">
    <label for="a11y-ai-aria-markup" class="a11y-ai-trigger__label">{{ __('Component markup') }}</label>
    <textarea
        id="a11y-ai-aria-markup"
        wire:model.defer="markup"
        rows="6"
        class="a11y-ai-trigger__textarea"
        placeholder="{{ __('Paste the HTML for your custom component.') }}"
    ></textarea>
    @error('markup')
        <p class="a11y-ai-trigger__field-error" role="alert">{{ $message }}</p>
    @enderror

    <label for="a11y-ai-aria-behavior" class="a11y-ai-trigger__label">{{ __('Behavior description') }}</label>
    <textarea
        id="a11y-ai-aria-behavior"
        wire:model.defer="behavior"
        rows="4"
        class="a11y-ai-trigger__textarea"
        placeholder="{{ __('Describe how the component behaves (interactions, states, focus flow).') }}"
    ></textarea>
    @error('behavior')
        <p class="a11y-ai-trigger__field-error" role="alert">{{ $message }}</p>
    @enderror

    <label for="a11y-ai-aria-framework" class="a11y-ai-trigger__label">{{ __('Framework (optional)') }}</label>
    <input
        id="a11y-ai-aria-framework"
        type="text"
        wire:model.defer="framework"
        class="a11y-ai-trigger__input"
        placeholder="livewire | react | vue"
    />

    <button
        type="button"
        wire:click="suggest"
        wire:loading.attr="disabled"
        wire:target="suggest"
        class="a11y-ai-trigger__button"
    >
        <span wire:loading.remove wire:target="suggest">{{ __('Suggest ARIA') }}</span>
        <span wire:loading wire:target="suggest">{{ __('Thinking…') }}</span>
    </button>

    @if ($error !== '')
        <div class="a11y-ai-trigger__error" role="alert">{{ $error }}</div>
    @endif

    @if ($suggestion !== null)
        <div class="a11y-ai-trigger__results" aria-label="{{ __('ARIA suggestion') }}">
            <p class="a11y-ai-trigger__result-role">
                <strong>{{ __('Role:') }}</strong>
                {{ $suggestion['role'] ?? __('(native semantics — no explicit role needed)') }}
            </p>

            @if (! empty($suggestion['attributes']))
                <h4 class="a11y-ai-trigger__result-heading">{{ __('Attributes') }}</h4>
                <ul>
                    @foreach ($suggestion['attributes'] as $attribute)
                        <li wire:key="attr-{{ $loop->index }}">
                            <code>{{ $attribute['name'] }}="{{ $attribute['value'] }}"</code>
                            — {{ $attribute['rationale'] }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if (! empty($suggestion['keyboard']))
                <h4 class="a11y-ai-trigger__result-heading">{{ __('Keyboard interactions') }}</h4>
                <ul>
                    @foreach ($suggestion['keyboard'] as $step)
                        <li wire:key="kbd-{{ $loop->index }}">{{ $step }}</li>
                    @endforeach
                </ul>
            @endif

            @if (! empty($suggestion['notes']))
                <h4 class="a11y-ai-trigger__result-heading">{{ __('Notes') }}</h4>
                <ul>
                    @foreach ($suggestion['notes'] as $note)
                        <li wire:key="note-{{ $loop->index }}">{{ $note }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
