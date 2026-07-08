<?php

/**
 * FormRequest for the ARIA-suggestion JSON endpoint.
 *
 *
 * @since      2.2.0
 */

declare(strict_types=1);

namespace ArtisanPack\Accessibility\Ai\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AriaSuggestionRequest extends FormRequest
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
            'markup' => ['required', 'string'],
            'behavior' => ['required', 'string'],
            'framework' => ['sometimes', 'string'],
            'existing_aria' => ['sometimes', 'array'],
        ];
    }
}
