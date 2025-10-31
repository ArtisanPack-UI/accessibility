<?php
/**
 * Accessible Color Generator
 *
 * Provides methods for generating accessible text colors based on a background color.
 *
 * @since      1.0.0
 * @package    ArtisanPackUI\Accessibility
 */

namespace ArtisanPackUI\Accessibility;

use ArtisanPackUI\Accessibility\A11y;

/**
 * Generates accessible text colors.
 *
 * This class provides methods to determine an appropriate text color with sufficient
 * contrast against a given background color, which can be provided as either a
 * hex code or a Tailwind CSS color name.
 *
 * @since 1.0.0
 */
class AccessibleColorGenerator
{
    private const RGB_MAX = 255;
    private const RGB_MIN = 0;

    private static array $shadeCache = [];
    public static int $cacheHits = 0;
    public static int $cacheMisses = 0;

    protected WcagValidator $wcagValidator;

    public function __construct(WcagValidator $wcagValidator = null)
    {
        $this->wcagValidator = $wcagValidator ?? new WcagValidator();
    }

    public static function getCacheHits(): int
    {
        return self::$cacheHits;
    }

    public static function getCacheMisses(): int
    {
        return self::$cacheMisses;
    }

    public static function clearCache(): void
    {
        self::$shadeCache = [];
        self::$cacheHits = 0;
        self::$cacheMisses = 0;
    }

	/**
	 * A map of Tailwind CSS colors to their hex values.
	 *
	 * This array provides a comprehensive mapping of the default Tailwind CSS
	 * color palette to their corresponding hexadecimal values.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected array $tailwindColors = [
		// Black & White
		'white'       => '#ffffff',
		'black'       => '#000000',

		// Slate
		'slate-50'    => '#f8fafc',
		'slate-100'   => '#f1f5f9',
		'slate-200'   => '#e2e8f0',
		'slate-300'   => '#cbd5e1',
		'slate-400'   => '#94a3b8',
		'slate-500'   => '#64748b',
		'slate-600'   => '#475569',
		'slate-700'   => '#334155',
		'slate-800'   => '#1e293b',
		'slate-900'   => '#0f172a',
		'slate-950'   => '#020617',

		// Gray
		'gray-50'     => '#f9fafb',
		'gray-100'    => '#f3f4f6',
		'gray-200'    => '#e5e7eb',
		'gray-300'    => '#d1d5db',
		'gray-400'    => '#9ca3af',
		'gray-500'    => '#6b7280',
		'gray-600'    => '#4b5563',
		'gray-700'    => '#374151',
		'gray-800'    => '#1f2937',
		'gray-900'    => '#111827',
		'gray-950'    => '#030712',

		// Zinc
		'zinc-50'     => '#fafafa',
		'zinc-100'    => '#f4f4f5',
		'zinc-200'    => '#e4e4e7',
		'zinc-300'    => '#d4d4d8',
		'zinc-400'    => '#a1a1aa',
		'zinc-500'    => '#71717a',
		'zinc-600'    => '#52525b',
		'zinc-700'    => '#3f3f46',
		'zinc-800'    => '#27272a',
		'zinc-900'    => '#18181b',
		'zinc-950'    => '#09090b',

		// Neutral
		'neutral-50'  => '#fafafa',
		'neutral-100' => '#f5f5f5',
		'neutral-200' => '#e5e5e5',
		'neutral-300' => '#d4d4d4',
		'neutral-400' => '#a3a3a3',
		'neutral-500' => '#737373',
		'neutral-600' => '#525252',
		'neutral-700' => '#404040',
		'neutral-800' => '#262626',
		'neutral-900' => '#171717',
		'neutral-950' => '#0a0a0a',

		// Stone
		'stone-50'    => '#fafaf9',
		'stone-100'   => '#f5f5f4',
		'stone-200'   => '#e7e5e4',
		'stone-300'   => '#d6d3d1',
		'stone-400'   => '#a8a29e',
		'stone-500'   => '#78716c',
		'stone-600'   => '#57534e',
		'stone-700'   => '#44403c',
		'stone-800'   => '#292524',
		'stone-900'   => '#1c1917',
		'stone-950'   => '#0c0a09',

		// Red
		'red-50'      => '#fef2f2',
		'red-100'     => '#fee2e2',
		'red-200'     => '#fecaca',
		'red-300'     => '#fca5a5',
		'red-400'     => '#f87171',
		'red-500'     => '#ef4444',
		'red-600'     => '#dc2626',
		'red-700'     => '#b91c1c',
		'red-800'     => '#991b1b',
		'red-900'     => '#7f1d1d',
		'red-950'     => '#450a0a',

		// Orange
		'orange-50'   => '#fff7ed',
		'orange-100'  => '#ffedd5',
		'orange-200'  => '#fed7aa',
		'orange-300'  => '#fdba74',
		'orange-400'  => '#fb923c',
		'orange-500'  => '#f97316',
		'orange-600'  => '#ea580c',
		'orange-700'  => '#c2410c',
		'orange-800'  => '#9a3412',
		'orange-900'  => '#7c2d12',
		'orange-950'  => '#431407',

		// Amber
		'amber-50'    => '#fffbeb',
		'amber-100'   => '#fef3c7',
		'amber-200'   => '#fde68a',
		'amber-300'   => '#fcd34d',
		'amber-400'   => '#fbbf24',
		'amber-500'   => '#f59e0b',
		'amber-600'   => '#d97706',
		'amber-700'   => '#b45309',
		'amber-800'   => '#92400e',
		'amber-900'   => '#78350f',
		'amber-950'   => '#451a03',

		// Yellow
		'yellow-50'   => '#fefce8',
		'yellow-100'  => '#fef9c3',
		'yellow-200'  => '#fef08a',
		'yellow-300'  => '#fde047',
		'yellow-400'  => '#facc15',
		'yellow-500'  => '#eab308',
		'yellow-600'  => '#ca8a04',
		'yellow-700'  => '#a16207',
		'yellow-800'  => '#854d0e',
		'yellow-900'  => '#713f12',
		'yellow-950'  => '#422006',

		// Lime
		'lime-50'     => '#f7fee7',
		'lime-100'    => '#ecfccb',
		'lime-200'    => '#d9f99d',
		'lime-300'    => '#bef264',
		'lime-400'    => '#a3e635',
		'lime-500'    => '#84cc16',
		'lime-600'    => '#65a30d',
		'lime-700'    => '#4d7c0f',
		'lime-800'    => '#3f6212',
		'lime-900'    => '#365314',
		'lime-950'    => '#1a2e05',

		// Green
		'green-50'    => '#f0fdf4',
		'green-100'   => '#dcfce7',
		'green-200'   => '#bbf7d0',
		'green-300'   => '#86efac',
		'green-400'   => '#4ade80',
		'green-500'   => '#22c55e',
		'green-600'   => '#16a34a',
		'green-700'   => '#15803d',
		'green-800'   => '#166534',
		'green-900'   => '#14532d',
		'green-950'   => '#052e16',

		// Emerald
		'emerald-50'  => '#ecfdf5',
		'emerald-100' => '#d1fae5',
		'emerald-200' => '#a7f3d0',
		'emerald-300' => '#6ee7b7',
		'emerald-400' => '#34d399',
		'emerald-500' => '#10b981',
		'emerald-600' => '#059669',
		'emerald-700' => '#047857',
		'emerald-800' => '#065f46',
		'emerald-900' => '#064e3b',
		'emerald-950' => '#022c22',

		// Teal
		'teal-50'     => '#f0fdfa',
		'teal-100'    => '#ccfbf1',
		'teal-200'    => '#99f6e4',
		'teal-300'    => '#5eead4',
		'teal-400'    => '#2dd4bf',
		'teal-500'    => '#14b8a6',
		'teal-600'    => '#0d9488',
		'teal-700'    => '#0f766e',
		'teal-800'    => '#115e59',
		'teal-900'    => '#134e4a',
		'teal-950'    => '#042f2e',

		// Cyan
		'cyan-50'     => '#ecfeff',
		'cyan-100'    => '#cffafe',
		'cyan-200'    => '#a5f3fc',
		'cyan-300'    => '#67e8f9',
		'cyan-400'    => '#22d3ee',
		'cyan-500'    => '#06b6d4',
		'cyan-600'    => '#0891b2',
		'cyan-700'    => '#0e7490',
		'cyan-800'    => '#155e75',
		'cyan-900'    => '#164e63',
		'cyan-950'    => '#083344',

		// Sky
		'sky-50'      => '#f0f9ff',
		'sky-100'     => '#e0f2fe',
		'sky-200'     => '#bae6fd',
		'sky-300'     => '#7dd3fc',
		'sky-400'     => '#38bdf8',
		'sky-500'     => '#0ea5e9',
		'sky-600'     => '#0284c7',
		'sky-700'     => '#0369a1',
		'sky-800'     => '#075985',
		'sky-900'     => '#0c4a6e',
		'sky-950'     => '#082f49',

		// Blue
		'blue-50'     => '#eff6ff',
		'blue-100'    => '#dbeafe',
		'blue-200'    => '#bfdbfe',
		'blue-300'    => '#93c5fd',
		'blue-400'    => '#60a5fa',
		'blue-500'    => '#3b82f6',
		'blue-600'    => '#2563eb',
		'blue-700'    => '#1d4ed8',
		'blue-800'    => '#1e40af',
		'blue-900'    => '#1e3a8a',
		'blue-950'    => '#172554',

		// Indigo
		'indigo-50'   => '#eef2ff',
		'indigo-100'  => '#e0e7ff',
		'indigo-200'  => '#c7d2fe',
		'indigo-300'  => '#a5b4fc',
		'indigo-400'  => '#818cf8',
		'indigo-500'  => '#6366f1',
		'indigo-600'  => '#4f46e5',
		'indigo-700'  => '#4338ca',
		'indigo-800'  => '#3730a3',
		'indigo-900'  => '#312e81',
		'indigo-950'  => '#1e1b4b',

		// Violet
		'violet-50'   => '#f5f3ff',
		'violet-100'  => '#ede9fe',
		'violet-200'  => '#ddd6fe',
		'violet-300'  => '#c4b5fd',
		'violet-400'  => '#a78bfa',
		'violet-500'  => '#8b5cf6',
		'violet-600'  => '#7c3aed',
		'violet-700'  => '#6d28d9',
		'violet-800'  => '#5b21b6',
		'violet-900'  => '#4c1d95',
		'violet-950'  => '#2e1065',

		// Purple
		'purple-50'   => '#faf5ff',
		'purple-100'  => '#f3e8ff',
		'purple-200'  => '#e9d5ff',
		'purple-300'  => '#d8b4fe',
		'purple-400'  => '#c084fc',
		'purple-500'  => '#a855f7',
		'purple-600'  => '#9333ea',
		'purple-700'  => '#7e22ce',
		'purple-800'  => '#6b21a8',
		'purple-900'  => '#581c87',
		'purple-950'  => '#3b0764',

		// Fuchsia
		'fuchsia-50'  => '#fdf4ff',
		'fuchsia-100' => '#fae8ff',
		'fuchsia-200' => '#f5d0fe',
		'fuchsia-300' => '#f0abfc',
		'fuchsia-400' => '#e879f9',
		'fuchsia-500' => '#d946ef',
		'fuchsia-600' => '#c026d3',
		'fuchsia-700' => '#a21caf',
		'fuchsia-800' => '#86198f',
		'fuchsia-900' => '#701a75',
		'fuchsia-950' => '#4a044e',

		// Pink
		'pink-50'     => '#fdf2f8',
		'pink-100'    => '#fce7f3',
		'pink-200'    => '#fbcfe8',
		'pink-300'    => '#f9a8d4',
		'pink-400'    => '#f472b6',
		'pink-500'    => '#ec4899',
		'pink-600'    => '#db2777',
		'pink-700'    => '#be185d',
		'pink-800'    => '#9d174d',
		'pink-900'    => '#831843',
		'pink-950'    => '#500724',

		// Rose
		'rose-50'     => '#fff1f2',
		'rose-100'    => '#ffe4e6',
		'rose-200'    => '#fecdd3',
		'rose-300'    => '#fda4af',
		'rose-400'    => '#fb7185',
		'rose-500'    => '#f43f5e',
		'rose-600'    => '#e11d48',
		'rose-700'    => '#be123c',
		'rose-800'    => '#9f1239',
		'rose-900'    => '#881337',
		'rose-950'    => '#4c0519',
	];

	/**
	 * Generates an accessible text color for a given background color.
	 *
	 * This method determines the best-contrasting text color. It can return
	 * either black or white, or it can generate a lighter/darker shade of
	 * the original background color that meets accessibility standards.
	 *
	 * @since 1.0.0
	 *
	 * @param string $backgroundColor The background color. Can be a hex code (e.g., '#3b82f6')
	 *                                or a Tailwind color name (e.g., 'blue-500').
	 * @param bool   $tint            Optional. If true, generates an accessible tint or shade.
	 *                                If false, returns black or white. Default false.
	 * @return string                 The generated accessible hex color string.
	 */
    public function generateAccessibleTextColor(string $backgroundColor, bool $tint = false, string $level = 'AA', bool $isLargeText = false): string
    {
        $hexColor = $this->getHexFromColorString($backgroundColor);

        if (!$hexColor) {
            return '#000000';
        }

        if ($tint) {
            return $this->findClosestAccessibleShade($hexColor, $level, $isLargeText);
        }

        $blackContrast = $this->wcagValidator->calculateContrastRatio($hexColor, '#000000');
        $whiteContrast = $this->wcagValidator->calculateContrastRatio($hexColor, '#FFFFFF');

        return $blackContrast > $whiteContrast ? '#000000' : '#FFFFFF';
    }

	/**
	 * Converts a color string (Tailwind or hex) to a hex code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $colorString The color string to process.
	 * @return string|null         The hex color string or null if not found.
	 */
	protected function getHexFromColorString( string $colorString ): ?string
	{
		$colorString = strtolower( trim( $colorString ) );

		// Check if it's already a hex color.
		if ( preg_match( '/^#([a-f0-9]{6}|[a-f0-9]{3})$/', $colorString ) ) {
			return $colorString;
		}

        // Check if it's an rgb color
        if (strpos($colorString, 'rgb') === 0) {
            preg_match('/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/', $colorString, $matches);
            if (count($matches) === 4) {
                return $this->rgbToHex((int)$matches[1], (int)$matches[2], (int)$matches[3]);
            }
        }

        // Check if it's an hsl color
        if (strpos($colorString, 'hsl') === 0) {
            preg_match('/hsl\(\s*(\d+)\s*,\s*(\d+)%\s*,\s*(\d+)%\s*\)/', $colorString, $matches);
            if (count($matches) === 4) {
                return $this->hslToHex((int)$matches[1], (int)$matches[2], (int)$matches[3]);
            }
        }

		// Check if it's a known Tailwind color.
		return $this->tailwindColors[ $colorString ] ?? null;
	}

    protected function rgbToHex(int $r, int $g, int $b): string
    {
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }

    protected function hslToHex(int $h, int $s, int $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        $r = $g = $b = 0;

        if (0 <= $h && $h < 60) {
            $r = $c;
            $g = $x;
        } else if (60 <= $h && $h < 120) {
            $r = $x;
            $g = $c;
        } else if (120 <= $h && $h < 180) {
            $g = $c;
            $b = $x;
        } else if (180 <= $h && $h < 240) {
            $g = $x;
            $b = $c;
        } else if (240 <= $h && $h < 300) {
            $r = $x;
            $b = $c;
        } else if (300 <= $h && $h < 360) {
            $r = $c;
            $b = $x;
        }

        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);

        return $this->rgbToHex($r, $g, $b);
    }

	/**
	 * Finds the closest accessible tint or shade of a base color.
	 *
	 * Iteratively lightens and darkens the base color until a variant with
	 * sufficient contrast (4.5:1) is found. If no variant is found, it
	 * falls back to black or white.
	 *
	 * @since 1.0.0
	 *
     * @param string $baseHex The hex color to find a variant for.
     * @param string $level The WCAG level to check against.
     * @param bool $isLargeText Whether the text is large or not.
	 * @return string          The accessible hex color variant.
	 */
    protected function findClosestAccessibleShade(string $baseHex, string $level = 'AA', bool $isLargeText = false): string
    {
        $cacheKey = "{$baseHex}-{$level}-" . ($isLargeText ? 'large' : 'normal');

        if (isset(self::$shadeCache[$cacheKey])) {
            self::$cacheHits++;
            return self::$shadeCache[$cacheKey];
        }

        self::$cacheMisses++;

        for ($i = 1; $i <= 20; $i++) {
            $step = $i / 20.0;

            $lighter = $this->adjustBrightness($baseHex, $step);
            if ($this->wcagValidator->checkContrast($baseHex, $lighter, $level, $isLargeText)) {
                return self::$shadeCache[$cacheKey] = $lighter;
            }

            $darker = $this->adjustBrightness($baseHex, -$step);
            if ($this->wcagValidator->checkContrast($baseHex, $darker, $level, $isLargeText)) {
                return self::$shadeCache[$cacheKey] = $darker;
            }
        }

        $blackContrast = $this->wcagValidator->calculateContrastRatio($baseHex, '#000000');
        $whiteContrast = $this->wcagValidator->calculateContrastRatio($baseHex, '#FFFFFF');

        return self::$shadeCache[$cacheKey] = $blackContrast > $whiteContrast ? '#000000' : '#FFFFFF';
    }
	/**
	 * Increases or decreases the brightness of a hex color.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hex    The hex color string.
	 * @param float  $factor The brightness factor. Positive for lighter, negative for darker.
	 *                       e.g., 0.1 for 10% lighter, -0.2 for 20% darker.
	 * @return string        The new hex color string.
	 */
	protected function adjustBrightness( string $hex, float $factor ): string
	{
		$hex = str_replace( '#', '', $hex );

		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		$r = round( max( self::RGB_MIN, min( self::RGB_MAX, $r + ( self::RGB_MAX * $factor ) ) ) );
		$g = round( max( self::RGB_MIN, min( self::RGB_MAX, $g + ( self::RGB_MAX * $factor ) ) ) );
		$b = round( max( self::RGB_MIN, min( self::RGB_MAX, $b + ( self::RGB_MAX * $factor ) ) ) );

		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT )
			. str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT )
			. str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}
}