<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

/**
 * Get Version History Request with enhanced security.
 *
 * This request class handles validation for version history operations
 * with comprehensive security measures and input sanitization.
 */
class GetVersionHistoryRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge(
            $this->getCommonRules(),
            $this->getProductSlugRules()
        );
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return array_merge(
            $this->getCommonMessages(),
            $this->getProductSlugMessages()
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'product_slug' => $this->sanitizeInput($this->input('product_slug')),
        ]);
    }
}
