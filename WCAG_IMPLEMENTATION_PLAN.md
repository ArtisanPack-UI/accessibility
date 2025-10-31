
# Plan: Implement WCAG 2.1 and 2.2 Support

This document outlines the plan to upgrade the accessibility package to support WCAG 2.1 and 2.2 guidelines.

## 1. Refactor to a Dedicated WCAG Validator

To better organize the code and prepare for the new features, we will start by refactoring the WCAG-related logic into a new, dedicated class.

- **Create `src/WcagValidator.php`:** This new class will encapsulate all the logic for WCAG contrast checking.
- **Move Logic from `A11y.php`:** Methods like `calculateContrastRatio`, `calculateRelativeLuminance`, and `a11yCheckContrastColor` will be moved from `A11y.php` to `WcagValidator.php`.
- **Update `A11y.php`:** The `A11y` class will be updated to use `WcagValidator.php` for all contrast-checking operations. This will be an internal change, and the public API of `A11y.php` will remain backward compatible.

## 2. Implement WCAG 2.1 Non-Text Contrast

WCAG 2.1 introduced a new requirement for non-text contrast. We will add support for this in our new `WcagValidator.php` class.

- **Add Non-Text Contrast Method:** A new method, `checkNonTextContrast`, will be added to `WcagValidator.php`. This method will check if the contrast ratio between two colors is at least 3:1.
- **Update `a11yCheckContrastColor`:** The existing `a11yCheckContrastColor` method (which will be moved to `WcagValidator.php`) will be updated to allow for a new 'level' of 'non-text'.

## 3. Add Large Text vs. Normal Text Distinction

The contrast requirements for large text are different from those for normal text. We will improve the existing logic to handle this distinction more accurately.

- **Improved Large Text Handling:** The `WcagValidator.php` will have a clear and well-documented implementation for checking large text contrast. According to WCAG, large text is defined as text that is 18pt (24px) or larger, or 14pt (18.66px) or larger and bold. The `checkContrast` method will be updated to reflect this.
- **Method Signature:** The `checkContrast` method in `WcagValidator.php` will have a signature like: `public function checkContrast(string $color1, string $color2, string $level = 'AA', bool $isLargeText = false): bool`.

## 4. Support for WCAG AAA Level and WCAG 2.2

We will add support for the AAA level contrast requirements and ensure our package is compliant with WCAG 2.2.

- **AAA Level Support:** The `checkContrast` method in `WcagValidator.php` will support an 'AAA' level, which will check for a contrast ratio of at least 7:1 for normal text and 4.5:1 for large text.
- **WCAG 2.2 Compliance:** WCAG 2.2 does not introduce new contrast ratio requirements. By implementing the requirements for WCAG 2.1 and the different levels (AA, AAA), we will also be compliant with the contrast-related aspects of WCAG 2.2. Other WCAG 2.2 success criteria (e.g., 2.4.11 Focus Not Obscured, 3.3.8 Accessible Authentication) are outside the scope of this package's color contrast functionality and will not be addressed in this task.

## 5. Research Spike: Enhanced Color Perception Algorithms

As an optional future enhancement, we will conduct a research spike to investigate more modern color perception algorithms.

- **Investigate APCA:** We will research the Accessible Perceptual Contrast Algorithm (APCA) as a potential future replacement for the current WCAG 2.x contrast formula.
- **Future Enhancement:** This will be treated as a research task, and the implementation of APCA will be considered for a future release. The priority for this task is to implement the WCAG 2.1/2.2 requirements.

## 6. Update `AccessibleColorGenerator.php`

The `AccessibleColorGenerator.php` class will be updated to leverage the new features in `WcagValidator.php`.

- **Use `WcagValidator.php`:** The `AccessibleColorGenerator` will be updated to use the new `WcagValidator` class.
- **Generate Colors for Different Levels:** The `generateAccessibleTextColor` method will be updated to allow generating colors that meet specific WCAG levels, including 'non-text' and 'AAA'.

## 7. Testing Strategy

A comprehensive test suite will be developed to ensure the new WCAG features are working correctly.

- **Create `tests/Unit/WcagValidatorTest.php`:** A new test file will be created for the `WcagValidator` class.
- **Add New Tests:** This test file will include tests for:
    - Non-text contrast (3:1 ratio).
    - AAA level contrast (7:1 ratio).
    - Large text vs. normal text contrast.
    - Edge cases and invalid inputs.
- **Update Existing Tests:** Existing tests in `tests/Unit/AccessibilityTest.php` will be updated to use the new `WcagValidator` class.

## 8. Documentation

The package's documentation will be updated to reflect the new WCAG support.

- **Create `docs/wcag-compliance.md`:** A new documentation file will be created to explain the new WCAG 2.1 and 2.2 support. This will include examples of how to use the new features.
- **Update Existing Documentation:** Other relevant documentation files will be updated to mention the new capabilities.

## 9. Backward Compatibility

Throughout this process, we will ensure that all existing public methods remain backward compatible.

- **No Breaking Changes:** The public API of the `A11y` class will not change. The refactoring to `WcagValidator.php` will be an internal change.
- **Deprecation Strategy:** If any methods are to be deprecated, they will be marked with `@deprecated` and will continue to function as before.
