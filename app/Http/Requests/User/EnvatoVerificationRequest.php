<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class EnvatoVerificationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'purchase_code' => ['required', 'string', 'max:255'],
            'product_slug' => ['nullable', 'string', 'max:255'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
