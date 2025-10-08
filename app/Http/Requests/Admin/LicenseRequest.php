<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenseRequest extends FormRequest
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
        return [
            'licenseKey' => ['required', 'string', 'max:255'],
            'productId' => ['required', 'exists:products,id'],
            'licenseType' => ['required', Rule::in(['regular', 'extended'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended', 'expired'])],
            'expiresAt' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'maxDomains' => ['nullable', 'integer', 'min:1'],
            'customer_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
