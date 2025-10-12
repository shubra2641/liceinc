<?php

declare(strict_types=1);

namespace App\Services\Installation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * License Validation Service
 * 
 * Handles license validation operations to reduce controller complexity.
 */
class LicenseValidationService
{
    /**
     * Validate license input.
     */
    public function validateLicenseInput(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'purchase_code' => 'required|string|min:10|max:255',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => $validator->errors()->first('purchase_code'),
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate admin input.
     */
    public function validateAdminInput(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => $validator->errors()->first(),
            ];
        }

        return ['valid' => true];
    }

    /**
     * Sanitize input to prevent XSS attacks.
     */
    public function sanitizeInput(mixed $input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (!is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Handle validation error.
     */
    public function handleValidationError(Request $request, string $message): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }
        
        return redirect()->back()->withErrors(['purchase_code' => $message])->withInput();
    }
}
