## ArtisanPack UI Accessibility

This package provides accessibility utilities for web applications, with a focus on color contrast and WCAG 2.0 compliance.

### Features

- **Color Contrast Checking**: Verify if text and background colors meet WCAG 2.0 standards (4.5:1 contrast ratio).

@verbatim
	<code-snippet name="Check color contrast" lang="php">
		// Check if two colors have sufficient contrast
		$hasGoodContrast = a11yCheckContrastColor('#3b82f6', '#ffffff'); // Returns true or false
	</code-snippet>
@endverbatim

- **Black or White Text Color**: Determine whether black or white text provides better contrast on a background.

@verbatim
	<code-snippet name="Get black or white text color" lang="php">
		// Returns 'black' or 'white' as a string
		$textColor = a11yCSSVarBlackOrWhite('#3b82f6');

		// Get the actual hex code (#000000 or #FFFFFF)
		$hexColor = a11yGetContrastColor('#3b82f6');
	</code-snippet>
@endverbatim

- **Accessible Text Color Generation**: Generate accessible text colors including tinted/shaded variants.

@verbatim
	<code-snippet name="Generate accessible text colors" lang="php">
		use ArtisanPack\Accessibility\AccessibleColorGenerator;

		$generator = new AccessibleColorGenerator();

		// Simple: returns black or white
		$textColor = $generator->generateAccessibleTextColor('#3b82f6');

		// Tinted: returns a lighter/darker shade of the background color
		$tintedColor = $generator->generateAccessibleTextColor('#3b82f6', true);

		// Or use the helper function
		$textColor = generateAccessibleTextColor('#3b82f6');
		$tintedColor = generateAccessibleTextColor('#3b82f6', true);
	</code-snippet>
@endverbatim

- **Tailwind CSS Color Support**: Use Tailwind color names instead of hex codes.

@verbatim
	<code-snippet name="Use Tailwind color names" lang="php">
		// All Tailwind default colors are supported
		$textColor = generateAccessibleTextColor('blue-500');
		$tintedColor = generateAccessibleTextColor('emerald-600', true);

		// Works with all helper functions
		$result = a11yCSSVarBlackOrWhite('red-500');
		$hasContrast = a11yCheckContrastColor('slate-900', 'slate-100');
	</code-snippet>
@endverbatim

- **Laravel Facade**: Access all functionality through the A11y facade.

@verbatim
	<code-snippet name="Use the A11y facade" lang="php">
		use ArtisanPack\Accessibility\Facades\A11y;

		$textColor = A11y::a11yCSSVarBlackOrWhite('#3b82f6');
		$hexColor = A11y::a11yGetContrastColor('#3b82f6');
		$hasContrast = A11y::a11yCheckContrastColor('#3b82f6', '#ffffff');
	</code-snippet>
@endverbatim

### Best Practices

1. **Always use hex colors or Tailwind names**: The package supports both hex codes (e.g., `#3b82f6`) and Tailwind color names (e.g., `blue-500`). Other color formats are not supported.

2. **Prefer helper functions for simplicity**: Use global helper functions like `a11yGetContrastColor()` and `generateAccessibleTextColor()` for cleaner code unless you need dependency injection.

3. **Use tinted colors when possible**: When generating text colors, prefer the tinted variant (`generateAccessibleTextColor($bg, true)`) to maintain visual harmony while ensuring accessibility.

4. **Check contrast before applying colors**: Use `a11yCheckContrastColor()` to verify that your chosen color combinations meet WCAG 2.0 standards before applying them.

5. **Contrast ratios**: This package uses a 4.5:1 contrast ratio threshold, which is the WCAG 2.0 AA standard for normal text. All color checks are based on this standard.
