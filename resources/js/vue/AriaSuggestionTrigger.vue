<!--
    Vue 3 trigger surface for the AriaSuggestionAgent. Ships in-package so
    Vue apps don't require any changes to `@artisanpack-ui/vue`.

    @since 2.2.0
-->
<script setup lang="ts">
import { ref } from 'vue';
import { ai11yHeaders } from './csrf';

export type AriaAttribute = {
    name: string;
    value: string;
    rationale: string;
};

export type AriaSuggestion = {
    role: string | null;
    attributes: AriaAttribute[];
    keyboard: string[];
    notes: string[];
};

const props = withDefaults(
    defineProps<{
        endpoint?: string;
        headers?: Record<string, string>;
    }>(),
    {
        endpoint: '/api/v1/a11y/ai/aria-suggestion',
        headers: () => ({}),
    },
);

const emit = defineEmits<{
    (event: 'result', suggestion: AriaSuggestion): void;
    (event: 'error', message: string): void;
}>();

const markup = ref('');
const behavior = ref('');
const framework = ref('');
const suggestion = ref<AriaSuggestion | null>(null);
const error = ref('');
const loading = ref(false);

async function suggest() {
    if (markup.value.trim() === '' || behavior.value.trim() === '') {
        error.value = 'Markup and behavior are required.';
        return;
    }

    loading.value = true;
    error.value = '';
    suggestion.value = null;

    try {
        const response = await fetch(props.endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: ai11yHeaders(props.headers),
            body: JSON.stringify({
                markup: markup.value,
                behavior: behavior.value,
                framework: framework.value,
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            const message = payload?.error ?? 'ARIA suggestion failed.';
            error.value = message;
            emit('error', message);
            return;
        }

        const next: AriaSuggestion = payload?.data ?? {
            role: null,
            attributes: [],
            keyboard: [],
            notes: [],
        };
        suggestion.value = next;
        emit('result', next);
    } catch (err) {
        const message = err instanceof Error ? err.message : 'ARIA suggestion failed.';
        error.value = message;
        emit('error', message);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="a11y-ai-trigger a11y-ai-trigger--aria">
        <label for="a11y-ai-aria-markup" class="a11y-ai-trigger__label">Component markup</label>
        <textarea
            id="a11y-ai-aria-markup"
            v-model="markup"
            :rows="6"
            class="a11y-ai-trigger__textarea"
            placeholder="Paste the HTML for your custom component."
        />

        <label for="a11y-ai-aria-behavior" class="a11y-ai-trigger__label">Behavior description</label>
        <textarea
            id="a11y-ai-aria-behavior"
            v-model="behavior"
            :rows="4"
            class="a11y-ai-trigger__textarea"
            placeholder="Describe how the component behaves (interactions, states, focus flow)."
        />

        <label for="a11y-ai-aria-framework" class="a11y-ai-trigger__label">Framework (optional)</label>
        <input
            id="a11y-ai-aria-framework"
            v-model="framework"
            type="text"
            class="a11y-ai-trigger__input"
            placeholder="livewire | react | vue"
        />

        <button
            type="button"
            class="a11y-ai-trigger__button"
            :disabled="loading"
            @click="suggest"
        >
            {{ loading ? 'Thinking…' : 'Suggest ARIA' }}
        </button>

        <div v-if="error" role="alert" class="a11y-ai-trigger__error">{{ error }}</div>

        <div v-if="suggestion" class="a11y-ai-trigger__results" aria-label="ARIA suggestion">
            <p class="a11y-ai-trigger__result-role">
                <strong>Role:</strong>
                {{ suggestion.role ?? '(native semantics — no explicit role needed)' }}
            </p>

            <template v-if="suggestion.attributes.length > 0">
                <h4 class="a11y-ai-trigger__result-heading">Attributes</h4>
                <ul>
                    <li
                        v-for="(attribute, index) in suggestion.attributes"
                        :key="`${attribute.name}-${index}`"
                    >
                        <code>{{ attribute.name }}="{{ attribute.value }}"</code>
                        — {{ attribute.rationale }}
                    </li>
                </ul>
            </template>

            <template v-if="suggestion.keyboard.length > 0">
                <h4 class="a11y-ai-trigger__result-heading">Keyboard interactions</h4>
                <ul>
                    <li v-for="(step, index) in suggestion.keyboard" :key="`kbd-${index}`">{{ step }}</li>
                </ul>
            </template>

            <template v-if="suggestion.notes.length > 0">
                <h4 class="a11y-ai-trigger__result-heading">Notes</h4>
                <ul>
                    <li v-for="(note, index) in suggestion.notes" :key="`note-${index}`">{{ note }}</li>
                </ul>
            </template>
        </div>
    </div>
</template>
