/**
 * React trigger surface for the AriaSuggestionAgent. Ships in-package so
 * React apps don't require any changes to `@artisanpack-ui/react`.
 *
 * @since 2.2.0
 */

import * as React from 'react';
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

export type AriaSuggestionTriggerProps = {
    endpoint?: string;
    headers?: Record<string, string>;
    onResult?: (suggestion: AriaSuggestion) => void;
    onError?: (message: string) => void;
};

const DEFAULT_ENDPOINT = '/api/v1/a11y/ai/aria-suggestion';

export function AriaSuggestionTrigger({
    endpoint = DEFAULT_ENDPOINT,
    headers,
    onResult,
    onError,
}: AriaSuggestionTriggerProps) {
    const [markup, setMarkup] = React.useState('');
    const [behavior, setBehavior] = React.useState('');
    const [framework, setFramework] = React.useState('');
    const [suggestion, setSuggestion] = React.useState<AriaSuggestion | null>(null);
    const [error, setError] = React.useState('');
    const [loading, setLoading] = React.useState(false);

    async function suggest() {
        if (markup.trim() === '' || behavior.trim() === '') {
            setError('Markup and behavior are required.');
            return;
        }

        setLoading(true);
        setError('');
        setSuggestion(null);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: ai11yHeaders(headers),
                body: JSON.stringify({ markup, behavior, framework }),
            });

            const payload = await response.json();

            if (!response.ok) {
                const message = payload?.error ?? 'ARIA suggestion failed.';
                setError(message);
                onError?.(message);
                return;
            }

            const next: AriaSuggestion = payload?.data ?? {
                role: null,
                attributes: [],
                keyboard: [],
                notes: [],
            };
            setSuggestion(next);
            onResult?.(next);
        } catch (err) {
            const message = err instanceof Error ? err.message : 'ARIA suggestion failed.';
            setError(message);
            onError?.(message);
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="a11y-ai-trigger a11y-ai-trigger--aria">
            <label htmlFor="a11y-ai-aria-markup" className="a11y-ai-trigger__label">
                Component markup
            </label>
            <textarea
                id="a11y-ai-aria-markup"
                rows={6}
                className="a11y-ai-trigger__textarea"
                value={markup}
                onChange={(event) => setMarkup(event.target.value)}
                placeholder="Paste the HTML for your custom component."
            />

            <label htmlFor="a11y-ai-aria-behavior" className="a11y-ai-trigger__label">
                Behavior description
            </label>
            <textarea
                id="a11y-ai-aria-behavior"
                rows={4}
                className="a11y-ai-trigger__textarea"
                value={behavior}
                onChange={(event) => setBehavior(event.target.value)}
                placeholder="Describe how the component behaves (interactions, states, focus flow)."
            />

            <label htmlFor="a11y-ai-aria-framework" className="a11y-ai-trigger__label">
                Framework (optional)
            </label>
            <input
                id="a11y-ai-aria-framework"
                type="text"
                className="a11y-ai-trigger__input"
                value={framework}
                onChange={(event) => setFramework(event.target.value)}
                placeholder="livewire | react | vue"
            />

            <button
                type="button"
                onClick={suggest}
                disabled={loading}
                className="a11y-ai-trigger__button"
            >
                {loading ? 'Thinking…' : 'Suggest ARIA'}
            </button>

            {error && (
                <div role="alert" className="a11y-ai-trigger__error">
                    {error}
                </div>
            )}

            {suggestion && (
                <div className="a11y-ai-trigger__results" aria-label="ARIA suggestion">
                    <p className="a11y-ai-trigger__result-role">
                        <strong>Role:</strong>{' '}
                        {suggestion.role ?? '(native semantics — no explicit role needed)'}
                    </p>

                    {suggestion.attributes.length > 0 && (
                        <>
                            <h4 className="a11y-ai-trigger__result-heading">Attributes</h4>
                            <ul>
                                {suggestion.attributes.map((attr, index) => (
                                    <li key={`${attr.name}-${index}`}>
                                        <code>
                                            {attr.name}=&quot;{attr.value}&quot;
                                        </code>{' '}
                                        — {attr.rationale}
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}

                    {suggestion.keyboard.length > 0 && (
                        <>
                            <h4 className="a11y-ai-trigger__result-heading">Keyboard interactions</h4>
                            <ul>
                                {suggestion.keyboard.map((step, index) => (
                                    <li key={`kbd-${index}`}>{step}</li>
                                ))}
                            </ul>
                        </>
                    )}

                    {suggestion.notes.length > 0 && (
                        <>
                            <h4 className="a11y-ai-trigger__result-heading">Notes</h4>
                            <ul>
                                {suggestion.notes.map((note, index) => (
                                    <li key={`note-${index}`}>{note}</li>
                                ))}
                            </ul>
                        </>
                    )}
                </div>
            )}
        </div>
    );
}

export default AriaSuggestionTrigger;
