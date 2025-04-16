# Digital Shopfront CMS Accessibility

This is the repository for the Digital Shopfront CMS Accessibility composer package. You can learn more
about [Digital Shopfront CMS here](https://gitlab.com/jacob-martella-web-design/digital-shopfront/digital-shopfront-core/digital-shopfront).

The package adds in accessibility functions for the CMS, such as color contrast checking, toast duration and more.

## Installation

You can install the accessibility package by running the following composer command.

`composer require digitalshopfront/accessibility`

## Usage

You can use any of the accessibility functions like this:

```
use Digitalshopfront\Accessibility\Facades\A11y as A11y;

echo A11y::a11yCSSVarBlackOrWhite('#38ed24');
```

You can also call any of the functions directly like this:

`echo A11y::a11yCSSVarBlackOrWhite('#38ed24');`

## Contributing

As an open source project, this package is open to contributions from anyone. Please [read through the contributing
guidelines](CONTRIBUTING.md) to learn more about how you can contribute to this project.
