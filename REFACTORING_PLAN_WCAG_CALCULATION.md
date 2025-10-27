# Refactoring Plan: WCAG Contrast Calculation Logic

**Version:** 1.0
**Date:** 2025-10-26
**Status:** Planning Phase

## Overview

This document outlines the refactoring plan for extracting duplicated WCAG contrast calculation logic from the `A11y` class into reusable private methods. The refactoring aims to improve code maintainability, reduce duplication, and fix a minor variable naming inconsistency.

## Current State Analysis

### Files Affected
- `src/A11y.php` - Contains duplicated calculation logic
- `tests/Unit/AccessibilityTest.php` - Contains existing test coverage (Note: Task description mentions `A11yTest.php` but actual file is `AccessibilityTest.php`)

### Duplicated Code Locations

The following logic is duplicated between two methods:

1. **`a11yGetContrastColor(string $hexColor): string`** (lines 56-92)
   - Calculates contrast between a background color and black
   - Returns either `#000000` or `#FFFFFF` based on which has better contrast
   - Uses 4.5:1 threshold (WCAG AA for normal text)

2. **`a11yCheckContrastColor(string $firstHexColor, string $secondHexColor): bool`** (lines 123-157)
   - Calculates contrast between any two colors
   - Returns boolean indicating if contrast meets 4.5:1 threshold
   - Contains a variable naming inconsistency: `$B3` should be `$B2` (line 133)

### Duplicated Logic Components

Both methods contain:

1. **RGB Extraction from Hex**
   ```php
   $R = hexdec(substr($hexColor, 1, 2));
   $G = hexdec(substr($hexColor, 3, 2));
   $B = hexdec(substr($hexColor, 5, 2));
   ```

2. **Relative Luminance Calculation** (WCAG 2.0 Formula)
   ```php
   $L = 0.2126 * pow($R / 255, 2.2) +
        0.7152 * pow($G / 255, 2.2) +
        0.0722 * pow($B / 255, 2.2);
   ```

3. **Contrast Ratio Calculation** (WCAG 2.0 Formula)
   ```php
   if ($L1 > $L2) {
       $contrastRatio = (float)(($L1 + 0.05) / ($L2 + 0.05));
   } else {
       $contrastRatio = (float)(($L2 + 0.05) / ($L1 + 0.05));
   }
   ```

### Existing Test Coverage

Current tests in `tests/Unit/AccessibilityTest.php`:

1. **Test: "returns correct text color for background color"**
   - Tests `a11yCSSVarBlackOrWhite()` which internally uses `a11yGetContrastColor()`
   - Test cases:
     - `#63FF1A` → black
     - `#0003BD` → white
     - `#DB95D4` → black
     - `#DBD739` → black
     - `#918F26` → black
     - `#D6444E` → black
     - `#CE414B` → white

2. **Test: "correctly checks the contrast between two colors"**
   - Tests `a11yCheckContrastColor()`
   - Test cases:
     - `#D6444E` vs `#000000` → true
     - `#C94049` vs `#000000` → false
     - `#2B37C9` vs `#000000` → false
     - `#2B37C9` vs `#FFFFFF` → true
     - `#2B37C9` vs `#262AFF` → false
     - `#2B37C9` vs `#FF7CE6` → false

## Issues Found During Analysis

### Bug: Variable Naming Inconsistency (ALREADY FIXED)

**Note:** A variable naming inconsistency (`$B3` instead of `$B2`) was identified in `src/A11y.php:133` during the initial audit. This has already been fixed in a previous branch and can be ignored for this refactoring task.

## Refactoring Strategy

### New Private Methods

We will extract three levels of abstraction:

#### 1. `hexToRgb(string $hexColor): array`
**Purpose:** Convert hex color to RGB array
**Input:** Hex color string (e.g., `#FF0000`)
**Output:** Associative array `['r' => int, 'g' => int, 'b' => int]`
**Visibility:** `private`

**Rationale:** This encapsulates the hex parsing logic and makes it reusable. Using an associative array provides clarity and prevents confusion about value order.

**Example:**
```php
private function hexToRgb(string $hexColor): array
{
    return [
        'r' => hexdec(substr($hexColor, 1, 2)),
        'g' => hexdec(substr($hexColor, 3, 2)),
        'b' => hexdec(substr($hexColor, 5, 2)),
    ];
}
```

#### 2. `calculateRelativeLuminance(array $rgb): float`
**Purpose:** Calculate relative luminance per WCAG 2.0
**Input:** RGB array from `hexToRgb()`
**Output:** Float value representing relative luminance (0-1 range)
**Visibility:** `private`

**Rationale:** This isolates the WCAG luminance formula, making it easier to verify correctness and potentially update if WCAG 3.0 formulas are needed in the future.

**Formula Reference:** WCAG 2.0 - https://www.w3.org/TR/WCAG20-TECHS/G17.html

**Example:**
```php
private function calculateRelativeLuminance(array $rgb): float
{
    return 0.2126 * pow($rgb['r'] / 255, 2.2) +
           0.7152 * pow($rgb['g'] / 255, 2.2) +
           0.0722 * pow($rgb['b'] / 255, 2.2);
}
```

#### 3. `calculateContrastRatio(string $color1, string $color2): float`
**Purpose:** Calculate WCAG 2.0 contrast ratio between two colors
**Input:** Two hex color strings
**Output:** Float representing contrast ratio (1-21 range)
**Visibility:** `private`

**Rationale:** This is the main extraction target mentioned in the task. It provides a single source of truth for contrast calculation.

**Formula Reference:** WCAG 2.0 - https://www.w3.org/TR/WCAG20-TECHS/G18.html

**Example:**
```php
private function calculateContrastRatio(string $color1, string $color2): float
{
    $rgb1 = $this->hexToRgb($color1);
    $rgb2 = $this->hexToRgb($color2);

    $L1 = $this->calculateRelativeLuminance($rgb1);
    $L2 = $this->calculateRelativeLuminance($rgb2);

    if ($L1 > $L2) {
        return (float)(($L1 + 0.05) / ($L2 + 0.05));
    } else {
        return (float)(($L2 + 0.05) / ($L1 + 0.05));
    }
}
```

### Refactored Public Methods

#### `a11yGetContrastColor(string $hexColor): string`

**Before:** Lines 56-92 (37 lines with duplicated logic)

**After:**
```php
public function a11yGetContrastColor(string $hexColor): string
{
    $blackContrastRatio = $this->calculateContrastRatio($hexColor, '#000000');

    if ($blackContrastRatio > 4.5) {
        return '#000000';
    } else {
        return '#FFFFFF';
    }
}
```

**Changes:**
- Reduced from 37 lines to 8 lines
- Removed all calculation logic
- Delegates to `calculateContrastRatio()`
- Maintains exact same functionality and return values

**Note:** Current implementation uses `> 4.5`, not `>= 4.5`. This will be preserved to maintain backward compatibility.

#### `a11yCheckContrastColor(string $firstHexColor, string $secondHexColor): bool`

**Before:** Lines 123-157 (35 lines with duplicated logic)

**After:**
```php
public function a11yCheckContrastColor(string $firstHexColor, string $secondHexColor): bool
{
    $contrastRatio = $this->calculateContrastRatio($firstHexColor, $secondHexColor);

    return $contrastRatio >= 4.5;
}
```

**Changes:**
- Reduced from 35 lines to 5 lines
- Removed all calculation logic
- Delegates to `calculateContrastRatio()`
- Maintains exact same functionality and return values

**Note:** This method uses `>= 4.5` (different from `a11yGetContrastColor()`). This will be preserved.

## Testing Strategy

### Regression Testing

**Objective:** Ensure all existing functionality remains unchanged.

**Approach:**
1. Run existing test suite before refactoring (baseline)
2. Run same tests after refactoring (comparison)
3. All tests must pass with identical results

**Existing Tests to Verify:**
- `tests/Unit/AccessibilityTest.php::returns correct text color for background color`
- `tests/Unit/AccessibilityTest.php::correctly checks the contrast between two colors`

**Command:**
```bash
vendor/bin/pest tests/Unit/AccessibilityTest.php
```

### New Unit Tests

Since the new private methods cannot be directly tested (they're private), we will use reflection to test them specifically.

**Test File:** `tests/Unit/AccessibilityTest.php` (add to existing file)

#### Test 1: `hexToRgb()` Method

**Test Name:** "converts hex color to RGB array correctly"

**Test Cases:**
```php
test('converts hex color to RGB array correctly', function () {
    $a11y = new A11y();
    $reflection = new ReflectionClass($a11y);
    $method = $reflection->getMethod('hexToRgb');
    $method->setAccessible(true);

    // Pure colors
    expect($method->invoke($a11y, '#FF0000'))->toEqual(['r' => 255, 'g' => 0, 'b' => 0])
        ->and($method->invoke($a11y, '#00FF00'))->toEqual(['r' => 0, 'g' => 255, 'b' => 0])
        ->and($method->invoke($a11y, '#0000FF'))->toEqual(['r' => 0, 'g' => 0, 'b' => 255])
        ->and($method->invoke($a11y, '#FFFFFF'))->toEqual(['r' => 255, 'g' => 255, 'b' => 255])
        ->and($method->invoke($a11y, '#000000'))->toEqual(['r' => 0, 'g' => 0, 'b' => 0]);

    // Mixed colors
    expect($method->invoke($a11y, '#D6444E'))->toEqual(['r' => 214, 'g' => 68, 'b' => 78])
        ->and($method->invoke($a11y, '#2B37C9'))->toEqual(['r' => 43, 'g' => 55, 'b' => 201]);
});
```

**Rationale:** Verifies basic hex parsing for pure colors and real-world colors from existing tests.

#### Test 2: `calculateRelativeLuminance()` Method

**Test Name:** "calculates relative luminance according to WCAG 2.0"

**Test Cases:**
```php
test('calculates relative luminance according to WCAG 2.0', function () {
    $a11y = new A11y();
    $reflection = new ReflectionClass($a11y);
    $method = $reflection->getMethod('calculateRelativeLuminance');
    $method->setAccessible(true);

    // White should have luminance of 1
    expect($method->invoke($a11y, ['r' => 255, 'g' => 255, 'b' => 255]))->toBeGreaterThan(0.99)
        ->and($method->invoke($a11y, ['r' => 255, 'g' => 255, 'b' => 255]))->toBeLessThanOrEqual(1.0);

    // Black should have luminance near 0
    expect($method->invoke($a11y, ['r' => 0, 'g' => 0, 'b' => 0]))->toBeLessThan(0.01)
        ->and($method->invoke($a11y, ['r' => 0, 'g' => 0, 'b' => 0]))->toBeGreaterThanOrEqual(0.0);

    // Red component should be weighted ~0.2126
    $redLuminance = $method->invoke($a11y, ['r' => 255, 'g' => 0, 'b' => 0]);
    expect($redLuminance)->toBeGreaterThan(0.20)
        ->and($redLuminance)->toBeLessThan(0.25);

    // Green component should be weighted ~0.7152 (highest)
    $greenLuminance = $method->invoke($a11y, ['r' => 0, 'g' => 255, 'b' => 0]);
    expect($greenLuminance)->toBeGreaterThan(0.70)
        ->and($greenLuminance)->toBeLessThan(0.75);

    // Blue component should be weighted ~0.0722 (lowest)
    $blueLuminance = $method->invoke($a11y, ['r' => 0, 'g' => 0, 'b' => 255]);
    expect($blueLuminance)->toBeGreaterThan(0.05)
        ->and($blueLuminance)->toBeLessThan(0.10);
});
```

**Rationale:** Verifies the WCAG formula is implemented correctly by testing edge cases (white/black) and verifying the correct weighting of RGB components.

#### Test 3: `calculateContrastRatio()` Method

**Test Name:** "calculates contrast ratio according to WCAG 2.0"

**Test Cases:**
```php
test('calculates contrast ratio according to WCAG 2.0', function () {
    $a11y = new A11y();
    $reflection = new ReflectionClass($a11y);
    $method = $reflection->getMethod('calculateContrastRatio');
    $method->setAccessible(true);

    // Maximum contrast (black vs white) should be 21:1
    $maxContrast = $method->invoke($a11y, '#000000', '#FFFFFF');
    expect($maxContrast)->toBeGreaterThan(20.9)
        ->and($maxContrast)->toBeLessThanOrEqual(21.0);

    // Minimum contrast (same color) should be 1:1
    expect($method->invoke($a11y, '#FF0000', '#FF0000'))->toEqual(1.0)
        ->and($method->invoke($a11y, '#ABCDEF', '#ABCDEF'))->toEqual(1.0);

    // Contrast should be symmetrical
    $ratio1 = $method->invoke($a11y, '#D6444E', '#000000');
    $ratio2 = $method->invoke($a11y, '#000000', '#D6444E');
    expect($ratio1)->toEqual($ratio2);

    // Verify known contrast ratios from existing tests
    // #D6444E vs #000000 should pass 4.5:1 (test expects true)
    expect($method->invoke($a11y, '#D6444E', '#000000'))->toBeGreaterThanOrEqual(4.5);

    // #C94049 vs #000000 should fail 4.5:1 (test expects false)
    expect($method->invoke($a11y, '#C94049', '#000000'))->toBeLessThan(4.5);

    // #2B37C9 vs #FFFFFF should pass 4.5:1 (test expects true)
    expect($method->invoke($a11y, '#2B37C9', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});
```

**Rationale:** Verifies contrast calculations against known values, edge cases, and ensures consistency with existing test expectations.

### Test Execution Plan

1. **Pre-refactoring:** Run full test suite and document results
   ```bash
   vendor/bin/pest
   ```

2. **During refactoring:** Run tests after each method extraction
   - After extracting `hexToRgb()` → run tests
   - After extracting `calculateRelativeLuminance()` → run tests
   - After extracting `calculateContrastRatio()` → run tests
   - After refactoring `a11yGetContrastColor()` → run tests
   - After refactoring `a11yCheckContrastColor()` → run tests

3. **Add new tests:** Add reflection-based unit tests for private methods

4. **Final verification:** Run complete test suite
   ```bash
   vendor/bin/pest --coverage
   ```

## Implementation Phases

### Phase 1: Preparation (5-10 minutes)

**Tasks:**
1. ~~Create feature branch~~ (Already in branch: `fix/variable-name-error`)
2. Ensure all existing tests pass
3. Document current test results as baseline
4. Review WCAG 2.0 specification for reference

**Verification:**
- All tests green before starting
- Ready to begin implementation

### Phase 2: Extract Helper Methods (15-20 minutes)

**Order of implementation:**
1. Add `hexToRgb()` private method
2. Add `calculateRelativeLuminance()` private method
3. Add `calculateContrastRatio()` private method

**Implementation approach:**
- Add each method at the end of the class (before closing brace)
- Do not modify existing public methods yet
- Add comprehensive PHPDoc comments for each private method

**Verification:**
- Code compiles without errors
- Existing tests still pass (new methods not used yet, so should not affect anything)

### Phase 3: Refactor Public Methods (15-20 minutes)

**Order of refactoring:**
1. Refactor `a11yGetContrastColor()` to use new methods
2. Run tests - verify behavior unchanged
3. Refactor `a11yCheckContrastColor()` to use new methods
4. Run tests - verify behavior unchanged

**Important Notes:**
- Preserve the different comparison operators (`>` vs `>=`)
- Maintain exact same return values
- This fixes the `$B3` variable bug automatically

**Verification:**
- All existing tests still pass
- Code is significantly shorter and cleaner
- No behavior changes

### Phase 4: Add Unit Tests (20-30 minutes)

**Tasks:**
1. Add reflection-based test for `hexToRgb()`
2. Add reflection-based test for `calculateRelativeLuminance()`
3. Add reflection-based test for `calculateContrastRatio()`
4. Run all tests

**Verification:**
- All new tests pass
- All existing tests still pass
- Test coverage maintained or improved

### Phase 5: Documentation & Cleanup (10-15 minutes)

**Tasks:**
1. Add PHPDoc comments to all new private methods
2. Update existing PHPDoc comments if needed
3. Verify code style matches project standards (PSR-12)
4. Run static analysis (if configured)

**Code Style Verification:**
```bash
# If using PHP CS Fixer
vendor/bin/php-cs-fixer fix src/A11y.php --dry-run --diff

# If using PHPCS
vendor/bin/phpcs src/A11y.php
```

**Verification:**
- All methods have proper documentation
- Code follows project style guide
- No static analysis warnings

### Phase 6: Final Testing & Commit (10 minutes)

**Tasks:**
1. Run complete test suite
2. Verify test coverage
3. Review all changes
4. Create commit with descriptive message

**Test Commands:**
```bash
vendor/bin/pest
vendor/bin/pest --coverage
```

**Commit Message Template:**
```
refactor: Extract duplicated WCAG calculation logic

- Create private method calculateContrastRatio() for shared logic
- Create private method calculateRelativeLuminance() for luminance calculation
- Create private method hexToRgb() for hex color parsing
- Refactor a11yGetContrastColor() to use shared methods
- Refactor a11yCheckContrastColor() to use shared methods
- Add unit tests for new private methods using reflection
- All existing functionality preserved and tests pass
```

**Verification:**
- All tests pass
- Changes committed
- Ready for pull request

## Expected Outcomes

### Code Quality Improvements

1. **Reduced Duplication:**
   - ~70 lines of duplicated code eliminated
   - Single source of truth for WCAG calculations

2. **Better Maintainability:**
   - Changes to WCAG formulas only need to be made in one place
   - Private methods can be tested independently
   - Easier to understand public method logic

3. **Improved Testability:**
   - Private methods can be tested via reflection
   - Better test coverage of calculation logic
   - Clearer test intent

### Metrics

**Before:**
- `a11yGetContrastColor()`: 37 lines
- `a11yCheckContrastColor()`: 35 lines
- Total: 72 lines with duplicated logic
- Test coverage: 2 tests (public methods only)

**After:**
- `hexToRgb()`: ~8 lines
- `calculateRelativeLuminance()`: ~5 lines
- `calculateContrastRatio()`: ~12 lines
- `a11yGetContrastColor()`: ~8 lines
- `a11yCheckContrastColor()`: ~5 lines
- Total: ~38 lines (47% reduction)
- Test coverage: 5 tests (2 existing + 3 new for private methods)

### No Breaking Changes

- All public method signatures remain unchanged
- All return values remain identical
- All existing tests pass without modification
- Backward compatibility 100% maintained

## Potential Challenges & Mitigation

### Challenge 1: Testing Private Methods

**Issue:** Private methods cannot be tested directly.

**Solution:** Use PHP Reflection API to access private methods in tests.

**Example:**
```php
$reflection = new ReflectionClass($a11y);
$method = $reflection->getMethod('calculateContrastRatio');
$method->setAccessible(true);
$result = $method->invoke($a11y, '#000000', '#FFFFFF');
```

**Alternative:** If reflection is not desired, could make methods `protected` instead of `private` to allow easier testing. However, `private` is preferred to enforce encapsulation.

### Challenge 2: Edge Cases

**Issue:** Current code doesn't validate hex color format.

**Current Behavior:**
- Invalid hex colors will cause `hexdec()` to return 0
- No exceptions thrown for malformed input

**Decision:**
- Maintain current behavior (no validation) to preserve backward compatibility
- Future enhancement could add validation with proper error handling

**Note for Future:** Consider adding validation in a separate PR:
```php
private function validateHexColor(string $hexColor): void
{
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $hexColor)) {
        throw new InvalidArgumentException("Invalid hex color: {$hexColor}");
    }
}
```

### Challenge 3: Floating Point Precision

**Issue:** Contrast calculations involve floating point math which may have precision issues.

**Current Behavior:**
- Uses `(float)` casting
- Compares with `> 4.5` and `>= 4.5`

**Mitigation:**
- Maintain existing casting and comparison approach
- Tests verify behavior matches existing implementation
- WCAG formulas are deterministic for given inputs

**Note:** This is not changed by refactoring, just documented for awareness.

## Future Enhancements

These are not part of this refactoring but could be considered in future work:

### 1. Support WCAG AAA Level

**Description:** Add methods for checking 7:1 contrast ratio (AAA level).

**API Addition:**
```php
public function a11yCheckContrastColorAAA(string $color1, string $color2): bool
{
    return $this->calculateContrastRatio($color1, $color2) >= 7.0;
}
```

### 2. Return Actual Contrast Ratio

**Description:** Provide public access to contrast ratio value.

**API Addition:**
```php
public function a11yGetContrastRatio(string $color1, string $color2): float
{
    return $this->calculateContrastRatio($color1, $color2);
}
```

### 3. Support Large Text Threshold

**Description:** WCAG has different thresholds for large text (3:1 for AA, 4.5:1 for AAA).

**API Addition:**
```php
public function a11yCheckContrastColorLargeText(string $color1, string $color2): bool
{
    return $this->calculateContrastRatio($color1, $color2) >= 3.0;
}
```

### 4. Hex Color Validation

**Description:** Add input validation with helpful error messages.

**Implementation:** Add validation in `hexToRgb()` method.

### 5. Support Additional Color Formats

**Description:** Support RGB, RGBA, HSL color formats.

**Implementation:** Add conversion methods and update `hexToRgb()` to handle multiple formats.

## References

### WCAG 2.0 Guidelines

- **Contrast Ratio Formula:** https://www.w3.org/TR/WCAG20-TECHS/G18.html
- **Relative Luminance:** https://www.w3.org/TR/WCAG20-TECHS/G17.html
- **Success Criterion 1.4.3 (AA):** https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html
- **Success Criterion 1.4.6 (AAA):** https://www.w3.org/WAI/WCAG21/Understanding/contrast-enhanced.html

### Project Resources

- **Main Class:** `src/A11y.php`
- **Tests:** `tests/Unit/AccessibilityTest.php`
- **Documentation:** `README.md`

### PHP Resources

- **Reflection API:** https://www.php.net/manual/en/book.reflection.php
- **Pest Testing:** https://pestphp.com/

## Checklist

Use this checklist during implementation:

### Pre-Implementation
- [ ] ~~Create feature branch~~ (Already in branch: `fix/variable-name-error`)
- [ ] Run existing tests and verify all pass
- [ ] Document baseline test results

### Implementation
- [ ] Add `hexToRgb()` private method with PHPDoc
- [ ] Add `calculateRelativeLuminance()` private method with PHPDoc
- [ ] Add `calculateContrastRatio()` private method with PHPDoc
- [ ] Verify code compiles and existing tests still pass
- [ ] Refactor `a11yGetContrastColor()` to use new methods
- [ ] Run tests - verify no behavior changes
- [ ] Refactor `a11yCheckContrastColor()` to use new methods
- [ ] Run tests - verify no behavior changes

### Testing
- [ ] Add reflection-based unit test for `hexToRgb()`
- [ ] Add reflection-based unit test for `calculateRelativeLuminance()`
- [ ] Add reflection-based unit test for `calculateContrastRatio()`
- [ ] Run all tests and verify they pass
- [ ] Check test coverage (should be maintained or improved)

### Code Quality
- [ ] Add/verify PHPDoc comments on all methods
- [ ] Verify code style matches project standards
- [ ] Run static analysis (if configured)
- [ ] Review for any remaining code smells

### Final Steps
- [ ] Run complete test suite one final time
- [ ] Review all changes
- [ ] Create descriptive commit message
- [ ] Commit changes
- [ ] Push to remote
- [ ] Create pull request
- [ ] Link pull request to related issue (if exists)

## Estimated Timeline

- **Total Time:** 75-105 minutes (~1.5-2 hours)
  - Phase 1 (Preparation): 5-10 minutes
  - Phase 2 (Extract Methods): 15-20 minutes
  - Phase 3 (Refactor Public Methods): 15-20 minutes
  - Phase 4 (Add Tests): 20-30 minutes
  - Phase 5 (Documentation): 10-15 minutes
  - Phase 6 (Final Testing): 10 minutes

## Risks & Rollback Plan

### Risks

1. **Low Risk:** Existing tests may fail due to calculation differences
   - **Likelihood:** Very low (calculations are identical)
   - **Impact:** Medium (would require debugging)

2. **Low Risk:** Floating point precision may cause test failures
   - **Likelihood:** Very low (existing code already handles this)
   - **Impact:** Low (adjust test assertions if needed)

3. **Very Low Risk:** Performance degradation from additional method calls
   - **Likelihood:** Very low (PHP method calls are fast)
   - **Impact:** Negligible (not in hot path)

### Rollback Plan

If issues arise:

1. **Immediate:**
   ```bash
   git reset --hard HEAD~1
   ```

2. **After Push:**
   ```bash
   git revert <commit-hash>
   ```

3. **Root Cause:** Investigate test failures, verify calculations match exactly

## Conclusion

This refactoring will significantly improve code quality by eliminating duplication and providing better test coverage. The implementation is low-risk with clear acceptance criteria and comprehensive testing. All changes maintain 100% backward compatibility.

The extracted private methods follow the Single Responsibility Principle and will make future enhancements (like WCAG 3.0 support) much easier to implement.

---

**Document Version:** 1.1
**Last Updated:** 2025-10-26
**Author:** PHPStorm Junie Audit Task
**Reviewer:** Jacob Martella

**Changes in v1.1:**
- Noted that $B3 variable naming bug already fixed in previous branch
- Updated to reflect already being in the `fix/variable-name-error` branch
- Removed branch creation step from implementation phases and checklist
