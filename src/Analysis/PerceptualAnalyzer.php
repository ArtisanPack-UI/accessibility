<?php

namespace ArtisanPackUI\Accessibility\Analysis;

class PerceptualAnalyzer
{
    // Reference white point (D65 illuminant)
    private const REF_X = 95.047;
    private const REF_Y = 100.000;
    private const REF_Z = 108.883;

    public function calculateDeltaE(string $hexColor1, string $hexColor2): float
    {
        $rgb1 = $this->hexToRgb($hexColor1);
        $rgb2 = $this->hexToRgb($hexColor2);

        $lab1 = $this->rgbToLab($rgb1['r'], $rgb1['g'], $rgb1['b']);
        $lab2 = $this->rgbToLab($rgb2['r'], $rgb2['g'], $rgb2['b']);

        return $this->deltaE2000($lab1, $lab2);
    }

    private function rgbToLab(int $r, int $g, int $b): array
    {
        $xyz = $this->rgbToXyz($r, $g, $b);
        return $this->xyzToLab($xyz['x'], $xyz['y'], $xyz['z']);
    }

    private function rgbToXyz(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        $r *= 100;
        $g *= 100;
        $b *= 100;

        $x = $r * 0.4124 + $g * 0.3576 + $b * 0.1805;
        $y = $r * 0.2126 + $g * 0.7152 + $b * 0.0722;
        $z = $r * 0.0193 + $g * 0.1192 + $b * 0.9505;

        return ['x' => $x, 'y' => $y, 'z' => $z];
    }

    private function xyzToLab(float $x, float $y, float $z): array
    {
        $x /= self::REF_X;
        $y /= self::REF_Y;
        $z /= self::REF_Z;

        $x = ($x > 0.008856) ? pow($x, 1 / 3) : (7.787 * $x) + 16 / 116;
        $y = ($y > 0.008856) ? pow($y, 1 / 3) : (7.787 * $y) + 16 / 116;
        $z = ($z > 0.008856) ? pow($z, 1 / 3) : (7.787 * $z) + 16 / 116;

        $l = (116 * $y) - 16;
        $a = 500 * ($x - $y);
        $b = 200 * ($y - $z);

        return ['l' => $l, 'a' => $a, 'b' => $b];
    }

    private function deltaE2000(array $lab1, array $lab2): float
    {
        // Constants for the formula
        $kL = 1;
        $kC = 1;
        $kH = 1;

        // Extract L, a, b values for both colors
        $L1 = $lab1['l'];
        $a1 = $lab1['a'];
        $b1 = $lab1['b'];

        $L2 = $lab2['l'];
        $a2 = $lab2['a'];
        $b2 = $lab2['b'];

        // Calculate C'1, C'2, h'1, h'2
        $C1 = sqrt($a1 * $a1 + $b1 * $b1);
        $C2 = sqrt($a2 * $a2 + $b2 * $b2);

        $C_avg = ($C1 + $C2) / 2;

        $G = 0.5 * (1 - sqrt(pow($C_avg, 7) / (pow($C_avg, 7) + pow(25, 7))));

        $a1_prime = $a1 * (1 + $G);
        $a2_prime = $a2 * (1 + $G);

        $C1_prime = sqrt($a1_prime * $a1_prime + $b1 * $b1);
        $C2_prime = sqrt($a2_prime * $a2_prime + $b2 * $b2);

        $h1_prime = $this->calculateHueAngle($a1_prime, $b1);
        $h2_prime = $this->calculateHueAngle($a2_prime, $b2);

        // Calculate Delta L', Delta C', Delta H'
        $deltaL_prime = $L2 - $L1;
        $deltaC_prime = $C2_prime - $C1_prime;

        $delta_h_prime = 0;
        if ($C1_prime * $C2_prime != 0) {
            $abs_diff_h = abs($h1_prime - $h2_prime);
            if ($abs_diff_h <= 180) {
                $delta_h_prime = $h2_prime - $h1_prime;
            } elseif ($abs_diff_h > 180 && $h2_prime <= $h1_prime) {
                $delta_h_prime = $h2_prime - $h1_prime + 360;
            } else {
                $delta_h_prime = $h2_prime - $h1_prime - 360;
            }
        }

        $deltaH_prime = 2 * sqrt($C1_prime * $C2_prime) * sin(deg2rad($delta_h_prime / 2));

        // Calculate L_prime_avg, C_prime_avg, H_prime_avg
        $L_prime_avg = ($L1 + $L2) / 2;
        $C_prime_avg = ($C1_prime + $C2_prime) / 2;

        $H_prime_avg = 0;
        if ($C1_prime * $C2_prime != 0) {
            $abs_diff_h = abs($h1_prime - $h2_prime);
            if ($abs_diff_h <= 180) {
                $H_prime_avg = ($h1_prime + $h2_prime) / 2;
            } elseif ($abs_diff_h > 180 && ($h1_prime + $h2_prime) < 360) {
                $H_prime_avg = ($h1_prime + $h2_prime + 360) / 2;
            } else {
                $H_prime_avg = ($h1_prime + $h2_prime - 360) / 2;
            }
        }

        // Calculate SL, SC, SH
        $T = 1 - 0.17 * cos(deg2rad($H_prime_avg - 30)) + 0.24 * cos(deg2rad(2 * $H_prime_avg)) + 0.32 * cos(deg2rad(3 * $H_prime_avg + 6)) - 0.20 * cos(deg2rad(4 * $H_prime_avg - 63));
        $SL = 1 + ((0.015 * pow($L_prime_avg - 50, 2)) / sqrt(20 + pow($L_prime_avg - 50, 2)));
        $SC = 1 + 0.045 * $C_prime_avg;
        $SH = 1 + 0.015 * $C_prime_avg * $T;

        // Calculate RT
        $delta_theta = 30 * exp(-pow(($H_prime_avg - 275) / 25, 2));
        $RC = 2 * sqrt(pow($C_prime_avg, 7) / (pow($C_prime_avg, 7) + pow(25, 7)));
        $RT = -$RC * sin(deg2rad(2 * $delta_theta));

        // Final Delta E 2000 calculation
        $termL = $deltaL_prime / ($kL * $SL);
        $termC = $deltaC_prime / ($kC * $SC);
        $termH = $deltaH_prime / ($kH * $SH);

        return sqrt(pow($termL, 2) + pow($termC, 2) + pow($termH, 2) + $RT * ($termC) * ($termH));
    }

    private function calculateHueAngle(float $a_prime, float $b): float
    {
        if ($a_prime == 0 && $b == 0) {
            return 0;
        }
        $angle = rad2deg(atan2($b, $a_prime));
        return ($angle >= 0) ? $angle : $angle + 360;
    }

    public function getComplementaryColor(string $hexColor): string
    {
        $hsl = $this->hexToHsl($hexColor);
        $hsl['h'] = ($hsl['h'] + 180) % 360;
        return $this->hslToHex($hsl['h'], $hsl['s'], $hsl['l']);
    }

    public function getAnalogousColors(string $hexColor): array
    {
        $hsl = $this->hexToHsl($hexColor);
        $h = $hsl['h'];

        $analogous1 = ($h + 30) % 360;
        $analogous2 = ($h - 30 + 360) % 360;

        return [
            $this->hslToHex($analogous1, $hsl['s'], $hsl['l']),
            $this->hslToHex($analogous2, $hsl['s'], $hsl['l']),
        ];
    }

    public function getTriadicColors(string $hexColor): array
    {
        $hsl = $this->hexToHsl($hexColor);
        $h = $hsl['h'];

        $triadic1 = ($h + 120) % 360;
        $triadic2 = ($h + 240) % 360;

        return [
            $this->hslToHex($triadic1, $hsl['s'], $hsl['l']),
            $this->hslToHex($triadic2, $hsl['s'], $hsl['l']),
        ];
    }

    private function hexToHsl(string $hexColor): array
    {
        $rgb = $this->hexToRgb($hexColor);
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $h = 0;
        $s = 0;
        $l = ($max + $min) / 2;

        if ($max != $min) {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }

        return ['h' => $h * 360, 's' => $s, 'l' => $l];
    }

    private function hslToHex(float $h, float $s, float $l): string
    {
        if ($s == 0) {
            $r = $g = $b = $l; // achromatic
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hueToRgb($p, $q, $h / 360 + 1/3);
            $g = $this->hueToRgb($p, $q, $h / 360);
            $b = $this->hueToRgb($p, $q, $h / 360 - 1/3);
        }

        return $this->rgbToHex((int)round($r * 255), (int)round($g * 255), (int)round($b * 255));
    }

    private function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    private function rgbToHex(int $r, int $g, int $b): string
    {
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }

    private function hexToRgb(string $hexColor): array
    {
        $hex = ltrim($hexColor, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
