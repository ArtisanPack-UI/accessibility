# API Reference

This document provides a reference for the REST API endpoints.

## OpenAPI Specification

```yaml
openapi: 3.0.0
info:
  title: Accessibility API
  version: 1.0.0
paths:
  /api/a11y/contrast-check:
    post:
      summary: Check contrast ratio
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                foreground:
                  type: string
                  example: '#000000'
                background:
                  type: string
                  example: '#FFFFFF'
      responses:
        '200':
          description: OK
          content:
            application/json:
              schema:
                type: object
                properties:
                  ratio:
                    type: number
                    example: 21
                  is_accessible:
                    type: boolean
                    example: true
  /api/a11y/generate-text-color:
    post:
      summary: Generate accessible text color
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                background_color:
                  type: string
                  example: '#000000'
      responses:
        '200':
          description: OK
          content:
            application/json:
              schema:
                type: object
                properties:
                  text_color:
                    type: string
                    example: '#FFFFFF'
  /api/a11y/audit-palette:
    post:
      summary: Audit color palette
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                colors:
                  type: array
                  items:
                    type: string
                    example: '#000000'
      responses:
        '200':
          description: OK
          content:
            application/json:
              schema:
                type: object
                properties:
                  results:
                    type: array
                    items:
                      type: object
                      properties:
                        foreground:
                          type: string
                          example: '#000000'
                        background:
                          type: string
                          example: '#FFFFFF'
                        ratio:
                          type: number
                          example: 21
                        is_accessible:
                          type: boolean
                          example: true
```
## AI Endpoints (2.2.0+)

Three additional endpoints wrap the AI agents introduced in 2.2.0. See the [AI Features guide](guides/ai-features.md) and the [reference API doc](reference/api-reference.md#ai-agents-220) for the agent-level details.

```yaml
paths:
  /api/v1/a11y/ai/content-analysis:
    post:
      summary: Analyse content for accessibility issues static rules miss
      security:
        - sanctumAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [content]
              properties:
                content:
                  type: string
                  example: 'Click here to read our documentation.'
                structure:
                  type: object
                  additionalProperties: true
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: object
                    properties:
                      issues:
                        type: array
                        items:
                          type: object
                          properties:
                            location: { type: string, example: 'link[0]' }
                            issue_type: { type: string, example: 'ambiguous-link-text' }
                            severity: { type: string, enum: [info, warning, error] }
                            suggested_fix: { type: string }

  /api/v1/a11y/ai/aria-suggestion:
    post:
      summary: Suggest ARIA roles/attributes for a custom component
      security:
        - sanctumAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [markup, behavior]
              properties:
                markup: { type: string }
                behavior: { type: string }
                framework: { type: string, example: 'livewire' }
                existing_aria: { type: object }
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: object
                    properties:
                      role: { type: [string, 'null'] }
                      attributes:
                        type: array
                        items:
                          type: object
                          properties:
                            name: { type: string }
                            value: { type: string }
                            rationale: { type: string }
                      keyboard: { type: array, items: { type: string } }
                      notes: { type: array, items: { type: string } }

  /api/v1/a11y/ai/contrast-explanation:
    post:
      summary: Explain a color-contrast failure in plain language
      security:
        - sanctumAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [foreground, background]
              properties:
                foreground: { type: string, example: '#777777' }
                background: { type: string, example: '#999999' }
                context:
                  type: string
                  enum: [body_text, large_text, ui]
                  default: body_text
                brand_palette:
                  type: array
                  items: { type: string }
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: object
                    properties:
                      explanation: { type: string }
                      current_ratio: { type: number }
                      required_ratio: { type: number }
                      suggested_alternatives:
                        type: array
                        items:
                          type: object
                          properties:
                            fg: { type: string }
                            bg: { type: string }
                            ratio: { type: number }
                            delta_from_original: { type: number }

components:
  securitySchemes:
    sanctumAuth:
      type: http
      scheme: bearer
      description: Laravel Sanctum. Stateful SPA requests must include the XSRF-TOKEN cookie replayed as X-XSRF-TOKEN.
```

### Error responses

All three endpoints return `{ "error": "human-readable message" }` with these status codes:

- **403** — feature toggle is off
- **422** — domain input error (bad payload, unresolvable color)
- **502** — provider transport failure (message is generic; provider identity never leaks)
- **503** — no AI credentials configured
