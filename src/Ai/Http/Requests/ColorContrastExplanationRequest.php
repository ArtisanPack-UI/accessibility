<?php

/**
 * FormRequest for the contrast-explanation JSON endpoint.
 *
 * @package    ArtisanPack_UI
 * @subpackage Accessibility
 *
 * @since      2.2.0
 */

declare( strict_types=1 );

namespace ArtisanPack\Accessibility\Ai\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ColorContrastExplanationRequest extends FormRequest
{
    /**
     * @since 2.2.0
     *
     * @return bool
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
            'foreground'    => [ 'required', 'string' ],
            'background'    => [ 'required', 'string' ],
            'context'       => [ 'sometimes', 'string', 'in:body_text,large_text,ui' ],
            'brand_palette' => [ 'sometimes', 'array' ],
        ];
    }

    /**
     * Provide the same defaulted-context behaviour the controller had
     * inline before.
     *
     * @since 2.2.0
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ( null === $this->input( 'context' ) ) {
            $this->merge( [ 'context' => 'body_text' ] );
        }
    }
}
