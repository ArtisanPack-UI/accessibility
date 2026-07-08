<!--
    Vue 3 trigger surface for the ContentAccessibilityAgent. Ships
    in-package so Vue apps don't require any changes to
    `@artisanpack-ui/vue`.

    @since 2.2.0
-->
<script setup lang="ts">
import { ref } from 'vue';
import { ai11yHeaders } from './csrf';

export type ContentIssue = {
    location: string;
    issue_type: string;
    severity: 'info' | 'warning' | 'error';
    suggested_fix: string;
};

const props = withDefaults(
    defineProps<{
        endpoint?: string;
        headers?: Record<string, string>;
    }>(),
    {
        endpoint: '/api/v1/a11y/ai/content-analysis',
        headers: () => ({}),
    },
);

const emit = defineEmits<{
    (event: 'result', issues: ContentIssue[]): void;
    (event: 'error', message: string): void;
}>();

const content = ref('');
const issues = ref<ContentIssue[]>([]);
const ran = ref(false);
const error = ref('');
const loading = ref(false);

async function analyze() {
    if (content.value.trim() === '') {
        error.value = 'Content is required.';
        return;
    }

    loading.value = true;
    error.value = '';
    issues.value = [];
    ran.value = false;

    try {
        const response = await fetch(props.endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: ai11yHeaders(props.headers),
            body: JSON.stringify({ content: content.value }),
        });

        const payload = await response.json();

        if (!response.ok) {
            const message = payload?.error ?? 'Content analysis failed.';
            error.value = message;
            emit('error', message);
            return;
        }

        const next: ContentIssue[] = payload?.data?.issues ?? [];
        issues.value = next;
        ran.value = true;
        emit('result', next);
    } catch (err) {
        const message = err instanceof Error ? err.message : 'Content analysis failed.';
        error.value = message;
        emit('error', message);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="a11y-ai-trigger a11y-ai-trigger--content">
        <label for="a11y-ai-content" class="a11y-ai-trigger__label">Content to analyze</label>
        <textarea
            id="a11y-ai-content"
            v-model="content"
            :rows="8"
            class="a11y-ai-trigger__textarea"
            placeholder="Paste the copy, HTML, or Markdown you want reviewed for accessibility issues."
        />

        <button
            type="button"
            class="a11y-ai-trigger__button"
            :disabled="loading"
            @click="analyze"
        >
            {{ loading ? 'Analyzing…' : 'Analyze content' }}
        </button>

        <div v-if="error" role="alert" class="a11y-ai-trigger__error">{{ error }}</div>

        <p v-if="ran && issues.length === 0" class="a11y-ai-trigger__empty">
            No content-level issues found.
        </p>

        <ul
            v-if="issues.length > 0"
            class="a11y-ai-trigger__results"
            aria-label="Content accessibility findings"
        >
            <li
                v-for="(issue, index) in issues"
                :key="`${issue.location}-${index}`"
                :class="`a11y-ai-trigger__result a11y-ai-trigger__result--${issue.severity}`"
            >
                <div class="a11y-ai-trigger__result-header">
                    <span class="a11y-ai-trigger__result-severity">{{ issue.severity }}</span>
                    <span class="a11y-ai-trigger__result-type">{{ issue.issue_type }}</span>
                    <span class="a11y-ai-trigger__result-location">{{ issue.location }}</span>
                </div>
                <p class="a11y-ai-trigger__result-fix">{{ issue.suggested_fix }}</p>
            </li>
        </ul>
    </div>
</template>
