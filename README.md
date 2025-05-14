# ArtisanPack UI Accessibility

This is the repository for the ArtisanPack UI Accessibility composer package.

The package adds in accessibility functions, such as color contrast checking, toast duration and more.

## Installation

You can install the accessibility package by running the following composer command.

`composer require artisanpack-ui/accessibility`

## Usage

You can use any of the accessibility functions like this:

```
use ArtisanPackUI\Accessibility\Facades\A11y as A11y;

echo A11y::a11yCSSVarBlackOrWhite('#38ed24');
```

You can also call any of the functions directly like this:

`echo A11y::a11yCSSVarBlackOrWhite('#38ed24');`

## Contributing

As an open source project, this package is open to contributions from anyone. Please [read through the contributing
guidelines](CONTRIBUTING.md) to learn more about how you can contribute to this project.
