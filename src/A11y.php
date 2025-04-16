<?php

namespace DigitalShopfront\Accessibility;
class A11y
{
    /**
     * Returns whether a text color should be black or white based on the background color.
     *
     * @param string $hexColor The hex code for the background color.
     * @return string
     * @since 1.0.0
     */
    public function a11yCSSVarBlackOrWhite( string $hexColor ): string
    {
        if ( '#000000' === $this->a11yGetContrastColor( $hexColor ) ) {
            return 'black';
        } else {
            return 'white';
        }
    }

    /**
     * Returns whether a text color should be black or white based on the background color.
     *
     * @param string $hexColor The hex code for the background color.
     * @return string
     * @since 1.0.0
     */
    public function a11yGetContrastColor( string $hexColor ): string
    {
        // hexColor RGB
        $R1 = hexdec( substr( $hexColor, 1, 2 ) );
        $G1 = hexdec( substr( $hexColor, 3, 2 ) );
        $B1 = hexdec( substr( $hexColor, 5, 2 ) );

        // Black RGB
        $blackColor   = "#000000";
        $R2BlackColor = hexdec( substr( $blackColor, 1, 2 ) );
        $G2BlackColor = hexdec( substr( $blackColor, 3, 2 ) );
        $B2BlackColor = hexdec( substr( $blackColor, 5, 2 ) );

        // Calc contrast ratio
        $L1 = 0.2126 * pow( $R1 / 255, 2.2 ) +
            0.7152 * pow( $G1 / 255, 2.2 ) +
            0.0722 * pow( $B1 / 255, 2.2 );

        $L2 = 0.2126 * pow( $R2BlackColor / 255, 2.2 ) +
            0.7152 * pow( $G2BlackColor / 255, 2.2 ) +
            0.0722 * pow( $B2BlackColor / 255, 2.2 );

        $contrastRatio = 0;
        if ( $L1 > $L2 ) {
            $contrastRatio = (float) ( ( $L1 + 0.05 ) / ( $L2 + 0.05 ) );
        } else {
            $contrastRatio = (float) ( ( $L2 + 0.05 ) / ( $L1 + 0.05 ) );
        }

        // If contrast is more than 5, return black color
        if ( $contrastRatio > 4.5 ) {
            return '#000000';
        } else {
            // if not, return white color.
            return '#FFFFFF';
        }
    }

    /**
     * Gets the user's setting for how long the toast element should stay on the screen.
     *
     * @return float|int
     * @since 1.0.0
     */
    public function getToastDuration(): float|int
    {
        $user = auth()->user();
        return $user->getSetting( 'a11y-toast-duration', 5 ) * 1000;
    }

    /**
     * Returns whether two given colors have the correct amount of contrast between them.
     *
     * @param string $firstHexColor  The first color to check.
     * @param string $secondHexColor The second color to check.
     * @return bool
     * @since 1.0.0
     */
    public function a11yCheckContrastColor( string $firstHexColor, string $secondHexColor ): bool
    {
        // hexColor RGB
        $R1 = hexdec( substr( $firstHexColor, 1, 2 ) );
        $G1 = hexdec( substr( $firstHexColor, 3, 2 ) );
        $B1 = hexdec( substr( $firstHexColor, 5, 2 ) );

        // Black RGB
        $R2 = hexdec( substr( $secondHexColor, 1, 2 ) );
        $G2 = hexdec( substr( $secondHexColor, 3, 2 ) );
        $B3 = hexdec( substr( $secondHexColor, 5, 2 ) );

        // Calc contrast ratio
        $L1 = 0.2126 * pow( $R1 / 255, 2.2 ) +
            0.7152 * pow( $G1 / 255, 2.2 ) +
            0.0722 * pow( $B1 / 255, 2.2 );

        $L2 = 0.2126 * pow( $R2 / 255, 2.2 ) +
            0.7152 * pow( $G2 / 255, 2.2 ) +
            0.0722 * pow( $B3 / 255, 2.2 );

        $contrastRatio = 0;
        if ( $L1 > $L2 ) {
            $contrastRatio = (float) ( ( $L1 + 0.05 ) / ( $L2 + 0.05 ) );
        } else {
            $contrastRatio = (float) ( ( $L2 + 0.05 ) / ( $L1 + 0.05 ) );
        }

        // If contrast is more than 5, return black color
        if ( $contrastRatio >= 4.5 ) {
            return true;
        }

        return false;
    }
}
