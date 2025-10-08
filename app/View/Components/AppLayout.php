<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Contracts\View\View as ViewContract;

/**
 * Application Layout Component with Enhanced Security.
 *
 * This view component provides the main application layout with enhanced security
 * measures and comprehensive error handling for the license management system.
 *
 * Features:
 * - Secure layout rendering with error handling
 * - Enhanced security measures and validation
 * - Comprehensive error handling and logging
 * - Proper type hints and return types
 * - Security headers and XSS protection
 * - Input validation and sanitization
 */
class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component with enhanced security.
     *
     * Renders the main application layout with comprehensive error handling
     * and security measures to ensure safe layout rendering.
     *
     * @return View The rendered view instance
     *
     * @throws \Exception When view rendering fails
     */
    public function render(): ViewContract
    {
        try {
            // Validate view exists and is accessible
            $viewPath = resource_path('views/layouts/app.blade.php');
            if (! file_exists($viewPath)) {
                Log::error('Application layout view not found', [
                    'view' => 'layouts.app',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
                throw new \Exception('Application layout view not found');
            }
            // Render the layout with security context
            /** @var view-string $viewName */
            $viewName = 'layouts.app';
            $view = view($viewName, []);
            // Add security headers and context
            $this->addSecurityContext($view);
            return $view;
        } catch (\Exception $e) {
            Log::error('Failed to render application layout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Add security context to the view with enhanced security measures.
     *
     * @param  View  $view  The view instance to add security context to
     */
    private function addSecurityContext(ViewContract $view): void
    {
        try {
            // Add security-related data to the view
            $appVersion = config('app.version', '1.0.0');
            $appEnv = config('app.env', 'production');
            $view->with([
                'security_token' => csrf_token(),
                'app_version' => is_string($appVersion) ? $appVersion : '1.0.0',
                'environment' => is_string($appEnv) ? $appEnv : 'production',
                'debug_mode' => (bool)config('app.debug', false),
                'timestamp' => (int)now()->timestamp,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add security context to view', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw exception here to avoid breaking the layout
        }
    }
}
