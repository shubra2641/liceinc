<?php
namespace App\Http\Controllers;
use Exception;
use Illuminate\Support\Facades\Log;
/**
 * Error Controller.
 *
 * Handles error responses and fallback routes for the application.
 * Provides proper error handling and logging for undefined routes.
 */
class ErrorController extends Controller
{
    /**
     * Handle authentication route not found.
     *
     * Returns a proper JSON error response when an authentication route
     * is not found. Logs the attempt for security monitoring.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authRouteNotFound()
    {
        try {
            Log::warning('Authentication route not found', [
                'url' => request()->url(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);
            return response()->json([
                'error' => 'Authentication route not found',
                'message' => 'The requested authentication route does not exist.',
                'timestamp' => now(),
            ], 404);
        } catch (Exception $e) {
            Log::error('Fallback route error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
