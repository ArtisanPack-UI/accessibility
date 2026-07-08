<?php

/**
 * Content accessibility agent.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Ai\Agents;

use ArtisanPackUI\Ai\Agents\ArtisanPackAgent;
use ArtisanPackUI\Ai\Contracts\AgentPrompter;
use ArtisanPackUI\Ai\Credentials\Credentials;
use ArtisanPackUI\Ai\Exceptions\FeatureError;

/**
 * Finds content-level accessibility issues that static rules miss:
 * ambiguous link text ("click here"), non-descriptive headings, jargon
 * without definitions, empty landmarks, and similar prose-shaped
 * problems that require semantic judgement.
 *
 * ## Input
 *
 * ```
 * [
 *   'content'   => string,           // required, plain text or HTML
 *   'structure' => [                 // optional but recommended
 *     'headings' => [ [ 'level' => int, 'text' => string ], ... ],
 *     'links'    => [ [ 'href' => string, 'text' => string ], ... ],
 *     'images'   => [ [ 'src' => string, 'alt' => string|null ], ... ],
 *   ],
 * ]
 * ```
 *
 * ## Output schema
 *
 * ```
 * {
 *   issues: [
 *     {
 *       location:      string, // e.g. "link[2]", "heading[3]", "paragraph[7]"
 *       issue_type:    string, // e.g. "ambiguous-link-text"
 *       severity:      string, // "info"|"warning"|"error"
 *       suggested_fix: string
 *     }
 *   ]
 * }
 * ```
 *
 *
 * @since      2.2.0
 */
class ContentAccessibilityAgent extends ArtisanPackAgent
{
    /**
     * {@inheritDoc}
     */
    public string $featureKey = 'a11y.content_analysis';

    /**
     * {@inheritDoc}
     */
    public string $package = 'artisanpack-ui/accessibility';

    /**
     * {@inheritDoc}
     */
    public string $defaultModel = 'claude-sonnet-4-6';

    /**
     * {@inheritDoc}
     */
    public function instructions(): string
    {
        return <<<'PROMPT'
You are an accessibility reviewer. Given a page's content and its structural summary, identify content-level accessibility issues that static rules miss.

Look for issues like:
- Ambiguous or non-descriptive link text ("click here", "read more", "link", bare URLs used as anchor text)
- Vague or duplicate headings that do not describe the section they introduce
- Skipped heading levels (e.g. h2 then h4 with no h3 in between)
- Jargon, acronyms, or specialized terms used without a plain-language definition on first use
- Images with alt text that repeats the filename, is empty for non-decorative content, or duplicates adjacent text verbatim
- Instructions that rely on sensory characteristics only ("click the red button", "see the image below") without a text label alternative
- Long paragraphs (>~80 words) that would benefit from being broken up for readability
- Reading-level mismatches for the apparent audience

Do NOT flag structural or attribute-only issues that automated tools already catch (missing lang, missing alt attribute, low contrast, form label absent) — the caller runs those separately.

For each issue, produce:
- location: a stable pointer using the structure indexes when possible (e.g. "link[2]" for the third link, "heading[1]" for the second heading, "paragraph[7]" for the eighth paragraph). Use "content" if the location is not tied to a structural element.
- issue_type: a short kebab-case slug (e.g. "ambiguous-link-text", "vague-heading", "undefined-jargon", "skipped-heading-level").
- severity: "error" for issues that block comprehension, "warning" for real friction, "info" for style-of-writing suggestions.
- suggested_fix: a single concrete replacement or action the author can take.

If no issues are found, return an empty issues array. Never invent issues to fill space.

Return a JSON object with key: issues (array).
PROMPT;
    }

    /**
     * {@inheritDoc}
     */
    public function outputSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['issues'],
            'properties' => [
                'issues' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['location', 'issue_type', 'severity', 'suggested_fix'],
                        'properties' => [
                            'location' => ['type' => 'string'],
                            'issue_type' => ['type' => 'string'],
                            'severity' => [
                                'type' => 'string',
                                'enum' => ['info', 'warning', 'error'],
                            ],
                            'suggested_fix' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(Credentials $credentials, string $model, string $instructions): array
    {
        $normalized = $this->normalizeInput($this->input());

        $prompter = app(AgentPrompter::class);

        $result = $prompter->prompt(
            credentials: $credentials,
            model: $model,
            instructions: $instructions,
            message: $this->buildMessage($normalized),
            outputSchema: $this->outputSchema(),
        );

        return [
            'output' => $this->validateOutput($result['output']),
            'input_tokens' => (int) ($result['input_tokens'] ?? 0),
            'output_tokens' => (int) ($result['output_tokens'] ?? 0),
        ];
    }

    /**
     * @since 2.2.0
     *
     * @param  mixed  $input  Raw input.
     * @return array{ content: string, structure: array<string, mixed> }
     */
    protected function normalizeInput(mixed $input): array
    {
        if (! is_array($input)) {
            throw FeatureError::forFeature(
                $this->featureKey,
                'input must be an array with a `content` key.',
            );
        }

        $content = isset($input['content']) && is_string($input['content']) ? $input['content'] : '';

        if (trim($content) === '') {
            throw FeatureError::forFeature($this->featureKey, '`content` must be a non-empty string.');
        }

        $structure = [];

        if (isset($input['structure']) && is_array($input['structure'])) {
            foreach (['headings', 'links', 'images'] as $key) {
                if (isset($input['structure'][$key]) && is_array($input['structure'][$key])) {
                    $structure[$key] = array_values($input['structure'][$key]);
                }
            }
        }

        return [
            'content' => $content,
            'structure' => $structure,
        ];
    }

    /**
     * @since 2.2.0
     *
     * @param  array{ content: string, structure: array<string, mixed> }  $normalized  Normalized input.
     * @return array<int, array<string, string>>
     */
    protected function buildMessage(array $normalized): array
    {
        $parts = [];

        if ($normalized['structure'] !== []) {
            $parts[] = [
                'type' => 'text',
                'text' => "Structural summary (JSON):\n".json_encode(
                    $normalized['structure'],
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
                ),
            ];
        }

        $parts[] = [
            'type' => 'text',
            'text' => "Content:\n".$normalized['content'],
        ];

        return $parts;
    }

    /**
     * @since 2.2.0
     *
     * @param  array<string, mixed>  $output  Decoded model output.
     * @return array{ issues: array<int, array{location: string, issue_type: string, severity: string, suggested_fix: string}> }
     */
    protected function validateOutput(array $output): array
    {
        $issues = [];

        if (isset($output['issues']) && is_array($output['issues'])) {
            foreach ($output['issues'] as $issue) {
                if (! is_array($issue)) {
                    continue;
                }

                $location = isset($issue['location']) ? (string) $issue['location'] : '';
                $issueType = isset($issue['issue_type']) ? (string) $issue['issue_type'] : '';
                $severity = isset($issue['severity']) ? (string) $issue['severity'] : 'warning';
                $suggestedFix = isset($issue['suggested_fix']) ? (string) $issue['suggested_fix'] : '';

                if (! in_array($severity, ['info', 'warning', 'error'], true)) {
                    $severity = 'warning';
                }

                if ($location === '' || $issueType === '') {
                    continue;
                }

                $issues[] = [
                    'location' => $location,
                    'issue_type' => $issueType,
                    'severity' => $severity,
                    'suggested_fix' => $suggestedFix,
                ];
            }
        }

        return ['issues' => $issues];
    }
}
