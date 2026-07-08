---
title: AI Features
---

# AI Features

Introduced in **2.2.0**, the accessibility package ships three AI-powered agents built on top of [`artisanpack-ui/ai`](https://github.com/ArtisanPack-UI/ai) v1.0. Each agent tackles an accessibility problem that static rules cannot solve well — semantic judgement of prose, ARIA guidance for custom widgets, and plain-language explanation of contrast failures.

The agents inherit everything the shared AI foundation provides: BYOK credentials, per-feature toggles, read-through caching, usage telemetry, and provider-agnostic streaming.

## Requirements

- `artisanpack-ui/accessibility` **2.2.0+**
- `artisanpack-ui/ai` **1.0+** (installed automatically as a `require` dep)
- Provider credentials configured via the AI package's credential store (see the AI package's BYOK guide) — the agents refuse to run when none are set
- Optional: `livewire/livewire` **3.0+** to use the shipped Livewire trigger components

## The three agents

### `ContentAccessibilityAgent`

**Feature key:** `a11y.content_analysis` • **Default model:** `claude-sonnet-4-6`

Analyses page copy or HTML for content-level accessibility problems that static rules miss: ambiguous link text (`click here`), vague or duplicate headings, undefined jargon, sensory-only instructions (`see the red box below`), and reading-level mismatches.

```php
use ArtisanPack\Accessibility\Ai\Agents\ContentAccessibilityAgent;

$output = ContentAccessibilityAgent::for( [
    'content'   => $post->body,
    'structure' => [
        'headings' => [ [ 'level' => 2, 'text' => 'Overview' ] ],
        'links'    => [ [ 'href' => '/pricing', 'text' => 'click here' ] ],
    ],
] )->run();

foreach ( $output['issues'] as $issue ) {
    // $issue = [
    //     'location'      => 'link[0]',
    //     'issue_type'    => 'ambiguous-link-text',
    //     'severity'      => 'warning',            // info | warning | error
    //     'suggested_fix' => 'Replace "click here" with a descriptive label.',
    // ]
}
```

### `AriaSuggestionAgent`

**Feature key:** `a11y.aria_suggestion` • **Default model:** `claude-sonnet-4-6`

Given a custom component's markup and a description of its behavior, returns a minimal ARIA recommendation — role, states/properties, and keyboard interactions. Honours the **first rule of ARIA**: when a native HTML element already provides the required semantics, the agent returns `role: null` and a note saying so, instead of pushing you toward unnecessary ARIA attributes.

```php
use ArtisanPack\Accessibility\Ai\Agents\AriaSuggestionAgent;

$output = AriaSuggestionAgent::for( [
    'markup'    => '<div class="toggle" tabindex="0"><span>Notifications</span></div>',
    'behavior'  => 'A rectangle the user can click or press Space to flip on and off.',
    'framework' => 'livewire',
] )->run();

// $output = [
//     'role'       => 'switch' | null,
//     'attributes' => [ [ 'name' => 'aria-checked', 'value' => 'false', 'rationale' => '…' ] ],
//     'keyboard'   => [ 'Space toggles the switch and updates aria-checked.' ],
//     'notes'      => [ 'Consider replacing the wrapper div with a native <button role="switch">…' ],
// ]
```

### `ColorContrastExplanationAgent`

**Feature key:** `a11y.contrast_explanation` • **Default model:** `claude-haiku-4-5`

Turns a failing color pair into a plain-language explanation and a set of accessible alternatives that preserve as much of the original brand intent as possible.

Contrast math is computed **locally** through the package's existing `WcagValidator` — the model never sees or invents ratios. Every model-suggested alternative is then re-checked with the same math and dropped if it still fails, so the caller can trust every entry in `suggested_alternatives`.

```php
use ArtisanPack\Accessibility\Ai\Agents\ColorContrastExplanationAgent;

$output = ColorContrastExplanationAgent::for( [
    'foreground' => '#777777',
    'background' => '#999999',
    'context'    => 'body_text',   // body_text | large_text | ui
] )->run();

// $output = [
//     'explanation'            => 'Both greys share almost the same lightness…',
//     'current_ratio'          => 1.42,
//     'required_ratio'         => 4.5,
//     'suggested_alternatives' => [
//         [ 'fg' => '#1a1a1a', 'bg' => '#ffffff', 'ratio' => 18.44, 'delta_from_original' => 0.7 ],
//         …
//     ],
// ]
```

Both hex codes (`#rrggbb`, `#rgb`) and Tailwind color names (`slate-900`, `blue-500`, etc.) are accepted as input.

## Framework surfaces

All three trigger components ship inside the accessibility package. Adding React or Vue support to your app does **not** require any changes to `@artisanpack-ui/react` or `@artisanpack-ui/vue`.

### Livewire

Three components are registered automatically when Livewire is installed:

```blade
<livewire:a11y-ai-content-analysis />
<livewire:a11y-ai-aria-suggestion />
<livewire:a11y-ai-contrast-explanation />
```

Each renders a self-contained form + result region and delegates the run to the corresponding agent, so it inherits the feature toggle, credentials, and cache.

### JSON API

Three endpoints are registered under the same route prefix as the existing accessibility API:

- `POST /api/v1/a11y/ai/content-analysis`
- `POST /api/v1/a11y/ai/aria-suggestion`
- `POST /api/v1/a11y/ai/contrast-explanation`

All three sit behind `auth:sanctum` + `throttle:api`, and use `FormRequest` classes for validation.

Response envelope:

```json
{ "data": { … agent output … } }
```

Error envelope:

```json
{ "error": "human-readable message" }
```

Status-code mapping:

| Status | Meaning                                            |
|--------|----------------------------------------------------|
| 200    | Success                                            |
| 403    | Feature toggle is off                              |
| 422    | Domain input error (bad payload, unresolvable color) |
| 502    | Provider transport failure (message is generic; provider identity is never leaked) |
| 503    | No AI credentials configured                       |

### React

TypeScript/TSX components live under `resources/js/react/`:

```tsx
import {
    ContentAnalysisTrigger,
    AriaSuggestionTrigger,
    ContrastExplanationTrigger,
} from 'artisanpack-ui-accessibility/resources/js/react';

<ContentAnalysisTrigger onResult={(issues) => console.log(issues)} />
```

A shared `csrf.ts` helper automatically reads the `XSRF-TOKEN` cookie and sends `X-XSRF-TOKEN` + `X-Requested-With: XMLHttpRequest`, so a Sanctum-authenticated SPA works with zero extra header wiring.

### Vue 3

The mirror-image Vue 3 SFCs live under `resources/js/vue/`:

```vue
<script setup lang="ts">
import { ContentAnalysisTrigger } from 'artisanpack-ui-accessibility/resources/js/vue';
</script>

<template>
    <ContentAnalysisTrigger @result="issues => console.log(issues)" />
</template>
```

## Sanctum SPA setup

For React or Vue triggers to reach the Sanctum-protected endpoints, complete the standard Sanctum SPA setup:

1. Configure `SANCTUM_STATEFUL_DOMAINS` in your `.env` to include your SPA origin.
2. From the SPA, GET `/sanctum/csrf-cookie` once to seed the `XSRF-TOKEN` cookie.
3. Ensure API requests are same-origin (or `withCredentials: true` with proper CORS).

The shipped `csrf.ts` helper handles the rest — you do not need to build the `X-XSRF-TOKEN` header manually.

## Feature toggles

Every agent is toggle-able independently through the shared `FeatureRegistry`. When a toggle is off, the agent throws `FeatureDisabledException` and the shipped surfaces render a friendly message instead of proxying to a provider.

Toggle the features on for the first time:

```php
use ArtisanPackUI\Ai\Contracts\FeatureRegistry;

app( FeatureRegistry::class )->enable( 'a11y.content_analysis' );
app( FeatureRegistry::class )->enable( 'a11y.aria_suggestion' );
app( FeatureRegistry::class )->enable( 'a11y.contrast_explanation' );
```

Or wire them through the AI package's Livewire admin dashboard.

## Error handling

The trigger surfaces (Livewire, HTTP, React/Vue) all share the same exception-mapping policy:

- `FeatureDisabledException` — friendly "disabled for this site" message.
- `MissingCredentialsException` — "AI credentials are not configured."
- `FeatureError` from the agent — surfaced verbatim (these are domain input errors like `` `content` must be non-empty ``).
- `FeatureError` from the prompter (transport failure) — collapsed to "The AI provider is currently unavailable." Provider identity and HTTP status codes are **never** leaked to end users; the raw exception is reported through Laravel's exception handler for observability.
- Any other `Throwable` — reported and shown as a generic "please try again" message.

## Related

- [Getting Started](Guides-Getting-Started) — installation and basic setup
- [API Reference](Reference-Api-Reference) — full method signatures
- [AI Guidelines](Guidelines-Ai-Guidelines) — best practices for AI systems generating accessible UI
