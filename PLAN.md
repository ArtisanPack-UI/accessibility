
# Plan: Replace Magic Numbers with Class Constants

## 1. Introduction

This document outlines the plan to replace hardcoded magic numbers in the codebase with named class constants. This will improve the maintainability, readability, and clarity of the code.

## 2. Task Details

**Title:** Replace Magic Numbers with Class Constants

**Description:** Replace hardcoded magic numbers throughout the codebase with properly named class constants to improve maintainability and clarity.

**Acceptance Criteria:**

- [ ] Define constants for WCAG contrast ratios (4.5, etc.)
- [ ] Define constants for RGB bounds (255, 0)
- [ ] Define constants for luminance calculation coefficients (0.2126, 0.7152, 0.0722)
- [ ] Replace all magic numbers with appropriate constants
- [ ] Update tests to use constants where appropriate
- [ ] Add documentation for constants

## 3. Affected Files

- `src/A11y.php`
- `src/AccessibleColorGenerator.php`
- `tests/Unit/AccessibilityTest.php`
- `tests/Unit/AccessibleColorGeneratorTest.php`

## 4. Execution Plan

### Step 1: Create a Constants Class

- Create a new file at `src/Accessibility/Constants.php`.
- This class will not be instantiated, but will house all the constants.
- Add a class docblock to explain the purpose of the class.

### Step 2: Define Constants

In the `Constants` class, define the following public constants:

- **WCAG Contrast Ratios:**
  - `WCAG_CONTRAST_AA = 4.5;`
  - `WCAG_CONTRAST_AAA = 7.0;`
- **RGB Color Bounds:**
  - `RGB_MAX = 255;`
  - `RGB_MIN = 0;`
- **Luminance Calculation Coefficients:**
  - `LUMINANCE_RED_COEFFICIENT = 0.2126;`
  - `LUMINANCE_GREEN_COEFFICIENT = 0.7152;`
  - `LUMINANCE_BLUE_COEFFICIENT = 0.0722;`

### Step 3: Refactor `src/A11y.php`

- Import the `Constants` class.
- In the `a11yGetContrastColor()` method:
  - Replace `255` with `Constants::RGB_MAX`.
  - Replace the luminance coefficients with their corresponding constants.
  - Replace `4.5` with `Constants::WCAG_CONTRAST_AA`.
- In the `a11yCheckContrastColor()` method:
  - Replace `255` with `Constants::RGB_MAX`.
  - Replace the luminance coefficients with their corresponding constants.
  - Replace `4.5` with `Constants::WCAG_CONTRAST_AA`.

### Step 4: Refactor `src/AccessibleColorGenerator.php`

- Import the `Constants` class.
- In the `adjustBrightness()` method:
  - Replace `255` with `Constants::RGB_MAX`.
  - Replace `0` with `Constants::RGB_MIN`.

### Step 5: Update Tests

- Review `tests/Unit/AccessibilityTest.php` and `tests/Unit/AccessibleColorGeneratorTest.php`.
- Although no magic numbers were identified in the initial review, a second review will be done to ensure that no instances were missed.

### Step 6: Documentation

- Add docblocks to each constant in `src/Accessibility/Constants.php` to explain what the constant represents.

## 5. Verification

- Run the test suite to ensure that all tests pass after the refactoring.
- Manually review the changes to confirm that all magic numbers have been replaced and the code is functioning as expected.
