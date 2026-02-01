---
title: ArtisanPack UI Accessibility Documentation
---

# ArtisanPack UI Accessibility Documentation

Welcome to the documentation for the ArtisanPack UI Accessibility package. This documentation will help you get started with the package and provide detailed information about its features and usage.

## Documentation Contents

### [Guides](Guides)
Complete guides to help you get started and effectively use the package:
- [Getting Started](Guides-Getting-Started): Installation and basic setup instructions
- [Usage Guide](Guides-Usage): Detailed examples of how to use the package's features
- [Artisan Commands](Commands): Using CLI to audit colors and generate palettes

### [Reference](Reference)
Comprehensive technical documentation and references:
- [API Reference](Reference-Api-Reference): Complete reference for all classes, methods, and functions
- [Tailwind Colors Reference](Reference-Tailwind-Colors): Complete list of supported Tailwind CSS color names

### [Guidelines](Guidelines)
Best practices and guidelines for accessible development:
- [AI Guidelines](Guidelines-Ai-Guidelines): Guidelines for AI systems generating accessible UI components

## Overview

The ArtisanPack UI Accessibility package provides tools for ensuring your web applications meet accessibility standards, particularly for color contrast. It includes:

1. **Color Contrast Utilities**: Methods to determine if text colors have sufficient contrast against background colors
2. **Accessible Text Color Generation**: Generate accessible text colors based on background colors
3. **Tailwind CSS Integration**: Support for Tailwind CSS color names
4. **User Accessibility Settings**: Manage user preferences for accessibility features
5. **Laravel Integration**: Seamless integration with Laravel applications

## Quick Start

### Installation

```bash
composer require artisanpack-ui/accessibility
```

### Basic Usage

```php
// Check if text should be black or white on a background
$textColor = a11yCSSVarBlackOrWhite('#3b82f6'); // Returns 'black' or 'white'

// Generate an accessible text color for a background
$accessibleColor = generateAccessibleTextColor('#3b82f6'); // Returns '#000000' or '#FFFFFF'

// Generate a tinted/shaded version that's accessible
$tintedColor = generateAccessibleTextColor('#3b82f6', true); // Returns a tinted/shaded hex color
```

For more detailed information, please refer to the specific documentation sections linked above.