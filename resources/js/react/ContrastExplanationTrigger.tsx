/**
 * React trigger surface for the ColorContrastExplanationAgent. Ships
 * in-package so React apps don't require any changes to
 * `@artisanpack-ui/react`.
 *
 * @since 2.2.0
 */

import * as React from 'react';
import { ai11yHeaders } from './csrf';

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

export type ContrastContext = 'body_text' | 'large_text' | 'ui';

export type ContrastExplanationTriggerProps = {
    endpoint?: string;
    headers?: Record<string, string>;
    initialForeground?: string;
    initialBackground?: string;
    initialContext?: ContrastContext;
    onResult?: (result: ContrastExplanation) => void;
    onError?: (message: string) => void;
};

const DEFAULT_ENDPOINT = '/api/v1/a11y/ai/contrast-explanation';

export function ContrastExplanationTrigger({
    endpoint = DEFAULT_ENDPOINT,
    headers,
    initialForeground = '#777777',
    initialBackground = '#ffffff',
    initialContext = 'body_text',
    onResult,
    onError,
}: ContrastExplanationTriggerProps) {
    const [foreground, setForeground] = React.useState(initialForeground);
    const [background, setBackground] = React.useState(initialBackground);
    const [context, setContext] = React.useState<ContrastContext>(initialContext);
    const [result, setResult] = React.useState<ContrastExplanation | null>(null);
    const [error, setError] = React.useState('');
    const [loading, setLoading] = React.useState(false);

    async function explain() {
        if (foreground.trim() === '' || background.trim() === '') {
            setError('Foreground and background are required.');
            return;
        }

        setLoading(true);
        setError('');
        setResult(null);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: ai11yHeaders(headers),
                body: JSON.stringify({ foreground, background, context }),
            });

            const payload = await response.json();

            if (!response.ok) {
                const message = payload?.error ?? 'Contrast explanation failed.';
                setError(message);
                onError?.(message);
                return;
            }

            const next: ContrastExplanation = payload?.data;
            setResult(next);
            onResult?.(next);
        } catch (err) {
            const message = err instanceof Error ? err.message : 'Contrast explanation failed.';
            setError(message);
            onError?.(message);
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="a11y-ai-trigger a11y-ai-trigger--contrast">
            <div className="a11y-ai-trigger__row">
                <div>
                    <label htmlFor="a11y-ai-fg" className="a11y-ai-trigger__label">
                        Foreground
                    </label>
                    <input
                        id="a11y-ai-fg"
                        type="text"
                        className="a11y-ai-trigger__input"
                        value={foreground}
                        onChange={(event) => setForeground(event.target.value)}
                    />
                </div>
                <div>
                    <label htmlFor="a11y-ai-bg" className="a11y-ai-trigger__label">
                        Background
                    </label>
                    <input
                        id="a11y-ai-bg"
                        type="text"
                        className="a11y-ai-trigger__input"
                        value={background}
                        onChange={(event) => setBackground(event.target.value)}
                    />
                </div>
                <div>
                    <label htmlFor="a11y-ai-ctx" className="a11y-ai-trigger__label">
                        Context
                    </label>
                    <select
                        id="a11y-ai-ctx"
                        className="a11y-ai-trigger__input"
                        value={context}
                        onChange={(event) => setContext(event.target.value as ContrastContext)}
                    >
                        <option value="body_text">Body text (4.5:1)</option>
                        <option value="large_text">Large text (3:1)</option>
                        <option value="ui">UI component (3:1)</option>
                    </select>
                </div>
            </div>

            <button
                type="button"
                onClick={explain}
                disabled={loading}
                className="a11y-ai-trigger__button"
            >
                {loading ? 'Explaining…' : 'Explain contrast'}
            </button>

            {error && (
                <div role="alert" className="a11y-ai-trigger__error">
                    {error}
                </div>
            )}

            {result && (
                <div className="a11y-ai-trigger__results" aria-label="Contrast explanation">
                    <p className="a11y-ai-trigger__result-ratios">
                        Measured: <strong>{result.current_ratio.toFixed(2)}:1</strong> / Required:{' '}
                        <strong>{result.required_ratio.toFixed(2)}:1</strong>
                    </p>
                    <p className="a11y-ai-trigger__result-explanation">{result.explanation}</p>

                    {result.suggested_alternatives.length > 0 && (
                        <>
                            <h4 className="a11y-ai-trigger__result-heading">Suggested alternatives</h4>
                            <ul>
                                {result.suggested_alternatives.map((alt, index) => (
                                    <li key={`${alt.fg}-${alt.bg}-${index}`}>
                                        <span
                                            className="a11y-ai-trigger__swatch"
                                            style={{ background: alt.bg, color: alt.fg }}
                                        >
                                            Aa
                                        </span>{' '}
                                        <code>{alt.fg}</code> / <code>{alt.bg}</code> —{' '}
                                        {alt.ratio.toFixed(2)}:1 (delta:{' '}
                                        {alt.delta_from_original.toFixed(2)})
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </div>
            )}
        </div>
    );
}

export default ContrastExplanationTrigger;
