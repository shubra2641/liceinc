<?php

namespace App\Services;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base Service Request with enhanced security.
 *
 * This base request class provides common functionality for service requests
 * with comprehensive security measures and input sanitization.
 */
class Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Base sanitization for all service requests
        $data = $this->all();
        $sanitizedData = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = $this->sanitizeInput($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }

        $this->merge($sanitizedData);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     */
    protected function sanitizeInput(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
