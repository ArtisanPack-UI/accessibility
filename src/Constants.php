<?php
/**
 * Constants
 *
 * This file contains constants used throughout the accessibility package.
 *
 * @since 2.0.0
 * @package ArtisanPackUI\Accessibility
 */

namespace ArtisanPackUI\Accessibility;

/**
 * Class Constants
 *
 * This class houses all the constants for the accessibility package.
 *
 * @since 2.0.0
 */
class Constants
{
    /**
     * The WCAG AA contrast ratio.
     *
     * @since 2.0.0
     * @var float
     */
    public const WCAG_CONTRAST_AA = 4.5;

    /**
     * The WCAG AAA contrast ratio.
     *
     * @since 2.0.0
     * @var float
     */
    public const WCAG_CONTRAST_AAA = 7.0;

    /**
     * The maximum value for an RGB color component.
     *
     * @since 2.0.0
     * @var int
     */
    public const RGB_MAX = 255;

    /**
     * The minimum value for an RGB color component.
     *
     * @since 2.0.0
     * @var int
     */
    public const RGB_MIN = 0;

    /**
     * The red coefficient for luminance calculations.
     *
     * @since 2.0.0
     * @var float
     */
    public const LUMINANCE_RED_COEFFICIENT = 0.2126;

    /**
     * The green coefficient for luminance calculations.
     *
     * @since 2.0.0
     * @var float
     */
    public const LUMINANCE_GREEN_COEFFICIENT = 0.7152;

    /**
     * The blue coefficient for luminance calculations.
     *
     * @since 2.0.0
     * @var float
     */
    public const LUMINANCE_BLUE_COEFFICIENT = 0.0722;
}
