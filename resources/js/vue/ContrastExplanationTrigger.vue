<!--
    Vue 3 trigger surface for the ColorContrastExplanationAgent. Ships
    in-package so Vue apps don't require any changes to
    `@artisanpack-ui/vue`.

    @since 2.2.0
-->
<script setup lang="ts">
import { ref } from 'vue';
import { ai11yHeaders } from './csrf';

export type ContrastContext = 'body_text' | 'large_text' | 'ui';

export type ContrastAlternative = {
    fg: string;
    bg: string;
    ratio: number;
    delta_from_original: number;
};

export type ContrastExplanation = {
    explanation: string;
    current_ratio: number;
    required_ratio: number;
    suggested_alternatives: ContrastAlternative[];
};

const props = withDefaults(
    defineProps<{
        endpoint?: string;
        headers?: Record<string, string>;
        initialForeground?: string;
        initialBackground?: string;
        initialContext?: ContrastContext;
    }>(),
    {
        endpoint: '/api/v1/a11y/ai/contrast-explanation',
        headers: () => ({}),
        initialForeground: '#777777',
        initialBackground: '#ffffff',
        initialContext: 'body_text',
    },
);

const emit = defineEmits<{
    (event: 'result', result: ContrastExplanation): void;
    (event: 'error', message: string): void;
}>();

const foreground = ref(props.initialForeground);
const background = ref(props.initialBackground);
const context = ref<ContrastContext>(props.initialContext);
const result = ref<ContrastExplanation | null>(null);
const error = ref('');
const loading = ref(false);

async function explain() {
    if (foreground.value.trim() === '' || background.value.trim() === '') {
        error.value = 'Foreground and background are required.';
        return;
    }

    loading.value = true;
    error.value = '';
    result.value = null;

    try {
        const response = await fetch(props.endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: ai11yHeaders(props.headers),
            body: JSON.stringify({
                foreground: foreground.value,
                background: background.value,
                context: context.value,
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            const message = payload?.error ?? 'Contrast explanation failed.';
            error.value = message;
            emit('error', message);
            return;
        }

        const next: ContrastExplanation = payload?.data;
        result.value = next;
        emit('result', next);
    } catch (err) {
        const message = err instanceof Error ? err.message : 'Contrast explanation failed.';
        error.value = message;
        emit('error', message);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="a11y-ai-trigger a11y-ai-trigger--contrast">
        <div class="a11y-ai-trigger__row">
            <div>
                <label for="a11y-ai-fg" class="a11y-ai-trigger__label">Foreground</label>
                <input
                    id="a11y-ai-fg"
                    v-model="foreground"
                    type="text"
                    class="a11y-ai-trigger__input"
                />
            </div>
            <div>
                <label for="a11y-ai-bg" class="a11y-ai-trigger__label">Background</label>
                <input
                    id="a11y-ai-bg"
                    v-model="background"
                    type="text"
                    class="a11y-ai-trigger__input"
                />
            </div>
            <div>
                <label for="a11y-ai-ctx" class="a11y-ai-trigger__label">Context</label>
                <select id="a11y-ai-ctx" v-model="context" class="a11y-ai-trigger__input">
                    <option value="body_text">Body text (4.5:1)</option>
                    <option value="large_text">Large text (3:1)</option>
                    <option value="ui">UI component (3:1)</option>
                </select>
            </div>
        </div>

        <button
            type="button"
            class="a11y-ai-trigger__button"
            :disabled="loading"
            @click="explain"
        >
            {{ loading ? 'Explaining…' : 'Explain contrast' }}
        </button>

        <div v-if="error" role="alert" class="a11y-ai-trigger__error">{{ error }}</div>

        <div v-if="result" class="a11y-ai-trigger__results" aria-label="Contrast explanation">
            <p class="a11y-ai-trigger__result-ratios">
                Measured: <strong>{{ result.current_ratio.toFixed(2) }}:1</strong> / Required:
                <strong>{{ result.required_ratio.toFixed(2) }}:1</strong>
            </p>
            <p class="a11y-ai-trigger__result-explanation">{{ result.explanation }}</p>

            <template v-if="result.suggested_alternatives.length > 0">
                <h4 class="a11y-ai-trigger__result-heading">Suggested alternatives</h4>
                <ul>
                    <li
                        v-for="(alt, index) in result.suggested_alternatives"
                        :key="`${alt.fg}-${alt.bg}-${index}`"
                    >
                        <span
                            class="a11y-ai-trigger__swatch"
                            :style="{ background: alt.bg, color: alt.fg }"
                            >Aa</span
                        >
                        <code>{{ alt.fg }}</code> / <code>{{ alt.bg }}</code> —
                        {{ alt.ratio.toFixed(2) }}:1 (delta:
                        {{ alt.delta_from_original.toFixed(2) }})
                    </li>
                </ul>
            </template>
        </div>
    </div>
</template>
