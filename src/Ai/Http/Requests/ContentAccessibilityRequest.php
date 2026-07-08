<?php

/**
 * FormRequest for the content-analysis JSON endpoint.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Ai\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentAccessibilityRequest extends FormRequest
{
    /**
     * @since 2.2.0
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @since 2.2.0
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'structure' => ['sometimes', 'array'],
        ];
    }
}
