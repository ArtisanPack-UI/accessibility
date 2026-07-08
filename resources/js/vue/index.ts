/**
 * Public entry point for the accessibility package's Vue trigger UIs.
 *
 * Vue apps import components directly from this package instead of the
 * `@artisanpack-ui/vue` package, so extending framework support to Vue
 * does not require any changes to the shared Vue package.
 *
 * @since 2.2.0
 */

export { default as ContentAnalysisTrigger } from './ContentAnalysisTrigger.vue';
export { default as AriaSuggestionTrigger } from './AriaSuggestionTrigger.vue';
export { default as ContrastExplanationTrigger } from './ContrastExplanationTrigger.vue';
