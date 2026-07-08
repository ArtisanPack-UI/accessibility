<div class="a11y-ai-trigger a11y-ai-trigger--content">
    <label for="a11y-ai-content" class="a11y-ai-trigger__label">
        {{ __('Content to analyze') }}
    </label>
    <textarea
        id="a11y-ai-content"
        wire:model.defer="content"
        rows="8"
        class="a11y-ai-trigger__textarea"
        placeholder="{{ __('Paste the copy, HTML, or Markdown you want reviewed for accessibility issues.') }}"
    ></textarea>
    @error('content')
        <p class="a11y-ai-trigger__field-error" role="alert">{{ $message }}</p>
    @enderror

    <button
        type="button"
        wire:click="analyze"
        wire:loading.attr="disabled"
        wire:target="analyze"
        class="a11y-ai-trigger__button"
    >
        <span wire:loading.remove wire:target="analyze">{{ __('Analyze content') }}</span>
        <span wire:loading wire:target="analyze">{{ __('Analyzing…') }}</span>
    </button>

    @if ($error !== '')
        <div class="a11y-ai-trigger__error" role="alert">{{ $error }}</div>
    @endif

    @if ($ran && count($issues) === 0)
        <p class="a11y-ai-trigger__empty">{{ __('No content-level issues found.') }}</p>
    @endif

    @if (count($issues) > 0)
        <ul class="a11y-ai-trigger__results" aria-label="{{ __('Content accessibility findings') }}">
            @foreach ($issues as $issue)
                <li wire:key="issue-{{ $loop->index }}" class="a11y-ai-trigger__result a11y-ai-trigger__result--{{ $issue['severity'] }}">
                    <div class="a11y-ai-trigger__result-header">
                        <span class="a11y-ai-trigger__result-severity">{{ $issue['severity'] }}</span>
                        <span class="a11y-ai-trigger__result-type">{{ $issue['issue_type'] }}</span>
                        <span class="a11y-ai-trigger__result-location">{{ $issue['location'] }}</span>
                    </div>
                    <p class="a11y-ai-trigger__result-fix">{{ $issue['suggested_fix'] }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</div>
