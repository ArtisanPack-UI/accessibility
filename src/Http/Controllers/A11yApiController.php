<?php

namespace ArtisanPack\Accessibility\Http\Controllers;

use ArtisanPack\Accessibility\Core\AccessibleColorGenerator;
use ArtisanPack\Accessibility\Core\WcagValidator;
use ArtisanPack\Accessibility\Http\Requests\AuditPaletteRequest;
use ArtisanPack\Accessibility\Http\Requests\ContrastCheckRequest;
use ArtisanPack\Accessibility\Http\Requests\GenerateTextColorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class A11yApiController extends Controller
{
    public function contrastCheck(ContrastCheckRequest $request, WcagValidator $validator): JsonResponse
    {
        $ratio = $validator->calculateContrastRatio($request->input('foreground'), $request->input('background'));

        return response()->json([
            'ratio' => $ratio,
            'is_accessible' => $validator->checkContrast($request->input('foreground'), $request->input('background')),
        ]);
    }

    public function generateTextColor(GenerateTextColorRequest $request, AccessibleColorGenerator $generator): JsonResponse
    {
        $textColor = $generator->generateAccessibleTextColor($request->input('background_color'));

        return response()->json([
            'text_color' => $textColor,
        ]);
    }

    public function auditPalette(AuditPaletteRequest $request, WcagValidator $validator): JsonResponse
    {
        $colors = $request->input('colors');
        $results = [];

        foreach ($colors as $color1) {
            foreach ($colors as $color2) {
                if ($color1 === $color2) {
                    continue;
                }

                $ratio = $validator->calculateContrastRatio($color1, $color2);
                $results[] = [
                    'foreground' => $color1,
                    'background' => $color2,
                    'ratio' => $ratio,
                    'is_accessible' => $validator->checkContrast($color1, $color2),
                ];
            }
        }

        return response()->json([
            'results' => $results,
        ]);
    }
}
