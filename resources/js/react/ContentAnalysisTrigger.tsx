/**
 * React trigger surface for the ContentAccessibilityAgent.
 *
 * This component ships INSIDE `artisanpack-ui/accessibility` so React
 * apps can consume the agent without any changes to
 * `@artisanpack-ui/react`. It POSTs to the JSON endpoint registered by
 * `A11yServiceProvider` and renders whatever the agent returns.
 *
 * @since 2.2.0
 */

import * as React from 'react';
import { ai11yHeaders } from './csrf';

export type ContentIssue = {
    location: string;
    issue_type: string;
    severity: 'info' | 'warning' | 'error';
    suggested_fix: string;
};

export type ContentAnalysisTriggerProps = {
    /** POST endpoint for the content-analysis controller. */
    endpoint?: string;
    /** Optional headers merged into the fetch call (CSRF, Authorization, …). */
    headers?: Record<string, string>;
    /** Fires when a run completes successfully. */
    onResult?: (issues: ContentIssue[]) => void;
    /** Fires on any run failure. */
    onError?: (message: string) => void;
};

const DEFAULT_ENDPOINT = '/api/v1/a11y/ai/content-analysis';

export function ContentAnalysisTrigger({
    endpoint = DEFAULT_ENDPOINT,
    headers,
    onResult,
    onError,
}: ContentAnalysisTriggerProps) {
    const [content, setContent] = React.useState('');
    const [issues, setIssues] = React.useState<ContentIssue[]>([]);
    const [ran, setRan] = React.useState(false);
    const [error, setError] = React.useState('');
    const [loading, setLoading] = React.useState(false);

    async function analyze() {
        if (content.trim() === '') {
            setError('Content is required.');
            return;
        }

        setLoading(true);
        setError('');
        setIssues([]);
        setRan(false);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: ai11yHeaders(headers),
                body: JSON.stringify({ content }),
            });

            const payload = await response.json();

            if (!response.ok) {
                const message = payload?.error ?? 'Content analysis failed.';
                setError(message);
                onError?.(message);
                return;
            }

            const nextIssues: ContentIssue[] = payload?.data?.issues ?? [];
            setIssues(nextIssues);
            setRan(true);
            onResult?.(nextIssues);
        } catch (err) {
            const message = err instanceof Error ? err.message : 'Content analysis failed.';
            setError(message);
            onError?.(message);
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="a11y-ai-trigger a11y-ai-trigger--content">
            <label htmlFor="a11y-ai-content" className="a11y-ai-trigger__label">
                Content to analyze
            </label>
            <textarea
                id="a11y-ai-content"
                className="a11y-ai-trigger__textarea"
                value={content}
                rows={8}
                onChange={(event) => setContent(event.target.value)}
                placeholder="Paste the copy, HTML, or Markdown you want reviewed for accessibility issues."
            />

            <button
                type="button"
                onClick={analyze}
                disabled={loading}
                className="a11y-ai-trigger__button"
            >
                {loading ? 'Analyzing…' : 'Analyze content'}
            </button>

            {error && (
                <div role="alert" className="a11y-ai-trigger__error">
                    {error}
                </div>
            )}

            {ran && issues.length === 0 && (
                <p className="a11y-ai-trigger__empty">No content-level issues found.</p>
            )}

            {issues.length > 0 && (
                <ul className="a11y-ai-trigger__results" aria-label="Content accessibility findings">
                    {issues.map((issue, index) => (
                        <li
                            key={`${issue.location}-${index}`}
                            className={`a11y-ai-trigger__result a11y-ai-trigger__result--${issue.severity}`}
                        >
                            <div className="a11y-ai-trigger__result-header">
                                <span className="a11y-ai-trigger__result-severity">{issue.severity}</span>
                                <span className="a11y-ai-trigger__result-type">{issue.issue_type}</span>
                                <span className="a11y-ai-trigger__result-location">{issue.location}</span>
                            </div>
                            <p className="a11y-ai-trigger__result-fix">{issue.suggested_fix}</p>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export default ContentAnalysisTrigger;
