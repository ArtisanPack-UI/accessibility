/**
 * Public entry point for the accessibility package's React trigger UIs.
 *
 * Consumers import components directly from this package instead of the
 * `@artisanpack-ui/react` package, so extending framework support to
 * React does not require any changes to the shared React package.
 *
 * @since 2.2.0
 */

export { ContentAnalysisTrigger, default as ContentAnalysisTriggerDefault } from './ContentAnalysisTrigger';
export type { ContentIssue, ContentAnalysisTriggerProps } from './ContentAnalysisTrigger';

export { AriaSuggestionTrigger, default as AriaSuggestionTriggerDefault } from './AriaSuggestionTrigger';
export type { AriaAttribute, AriaSuggestion, AriaSuggestionTriggerProps } from './AriaSuggestionTrigger';

export {
    ContrastExplanationTrigger,
    default as ContrastExplanationTriggerDefault,
} from './ContrastExplanationTrigger';
export type {
    ContrastAlternative,
    ContrastContext,
    ContrastExplanation,
    ContrastExplanationTriggerProps,
} from './ContrastExplanationTrigger';
