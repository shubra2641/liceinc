<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

/**
 * Product Update Latest Version Request with enhanced security.
 *
 * This request class handles validation for latest version operations
 * with comprehensive security measures and input sanitization.
 */
class ProductUpdateLatestVersionRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge(
            $this->getCommonRules(),
            $this->getProductIdRules()
        );
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return array_merge(
            $this->getCommonMessages(),
            $this->getProductIdMessages()
        );
    }
}
