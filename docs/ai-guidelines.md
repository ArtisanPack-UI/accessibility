---
title: AI Guidelines
---

# AI Guidelines

You can add these guidelines to the rest of your guidelines for your editor/AI model of your choice so that your AI knows how to use this package.

## Accessibility (artisanpack-ui-accessibility)

**Primary Goal**: To ensure that all generated UI components and interfaces are fully accessible and adhere to modern web accessibility standards.

### Core Principles for the AI:

**WCAG Compliance**: All generated components must strive for compliance with Web Content Accessibility Guidelines (WCAG) 2.1 AA standards.

**Semantic HTML**: Use HTML elements for their intended purpose. For example, use `<button>` for actions and `<a>` for navigation.

**Keyboard Navigability**: All interactive elements must be fully operable with a keyboard. This includes visible focus states for all focusable elements.

**Color Contrast**: Ensure that all text has sufficient color contrast against its background to be readable by users with low vision.

**Text Alternatives**: All non-text content (e.g., images, icons) must have a text alternative.

### Specific Instructions for the AI:

When generating UI components, automatically include appropriate ARIA (Accessible Rich Internet Applications) attributes, such as `role`, `aria-label`, and `aria-hidden`, where necessary.

For all form inputs, generate a corresponding `<label>` element and associate it with the input using the `for` attribute.

When suggesting color combinations, use the `a11yCheckContrastColor` function from the accessibility package to validate that the contrast ratio meets WCAG AA standards.

For any interactive elements, such as dropdowns or modals, ensure that focus is properly managed. When a modal is opened, focus should be trapped within it, and when closed, focus should return to the element that triggered it.

When generating `<img>` tags, always include a descriptive `alt` attribute. If the image is purely decorative, use an empty `alt` attribute (`alt=""`).