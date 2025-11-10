# Color Analysis Tools

This package provides a set of tools for analyzing colors for accessibility and perceptual uniformity.

## Color Blindness Simulation

You can simulate how colors appear to people with different types of color blindness.

```php
use ArtisanPackUI\Accessibility\Analysis\ColorBlindnessSimulator;

$simulator = new ColorBlindnessSimulator();

$protanopia = $simulator->simulateProtanopia('#ff0000');
$deuteranopia = $simulator->simulateDeuteranopia('#ff0000');
$tritanopia = $simulator->simulateTritanopia('#ff0000');
```

## Perceptual Color Difference

You can calculate the perceptual difference between two colors using the Delta E 2000 formula.

```php
use ArtisanPackUI\Accessibility\Analysis\PerceptualAnalyzer;

$analyzer = new PerceptualAnalyzer();

$deltaE = $analyzer->calculateDeltaE('#ff0000', '#c80000');
```

## Accessibility Scoring

You can calculate an accessibility score for a color combination on a scale of 0-100.

```php
use ArtisanPackUI\Accessibility\Analysis\AccessibilityScorer;

$scorer = new AccessibilityScorer();

$score = $scorer->calculateScore('#000000', '#ffffff');
```

## Color Harmony

You can get complementary, analogous, and triadic colors for a given color.

```php
use ArtisanPackUI\Accessibility\Analysis\PerceptualAnalyzer;

$analyzer = new PerceptualAnalyzer();

$complementary = $analyzer->getComplementaryColor('#ff0000');
$analogous = $analyzer->getAnalogousColors('#ff0000');
$triadic = $analyzer->getTriadicColors('#ff0000');
```

## Comprehensive Analysis Report

You can generate a comprehensive analysis report for a color combination.

```php
use ArtisanPackUI\Accessibility\Analysis\ReportGenerator;

$generator = new ReportGenerator();

$report = $generator->generate('#ff0000', '#ffffff');
```
