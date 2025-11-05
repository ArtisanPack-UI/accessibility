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