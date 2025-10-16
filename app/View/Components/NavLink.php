<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Helpers\SecureFileHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Navigation Link Component with Enhanced Security.
 *
 * This view component provides navigation link functionality with enhanced security
 * measures and comprehensive error handling for the license management system.
 *
 * Features:
 * - Secure navigation link rendering with error handling
 * - Enhanced security measures and input validation
 * - Comprehensive error handling and logging
 * - Proper type hints and return types
 * - XSS protection and input sanitization
 * - Active state management with validation
 */
class NavLink extends Component
{
    /**
     * The active state of the navigation link.
     */
    public bool $active;

    /**
     * The CSS classes for the navigation link.
     */
    public string $classes;

    /**
     * Create a new component instance with enhanced security.
     *
     * @param  bool  $active  Whether the navigation link is active
     *
     * @throws \InvalidArgumentException When invalid active state is provided
     */
    public function __construct(bool $active = false)
    {
        try {
            // Validate and sanitize input
            $this->active = $this->validateActiveState($active);
            $this->classes = $this->getClasses();
        } catch (\Exception $e) {
            Log::error('Failed to initialize NavLink component', [
                'active' => $active,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate the active state with enhanced security.
     *
     * @param  mixed  $active  The active state to validate
     *
     * @return bool The validated active state
     *
     * @throws \InvalidArgumentException When invalid active state is provided
     */
    private function validateActiveState($active): bool
    {
        // Convert to boolean and validate
        if (is_bool($active)) {
            return $active;
        }
        if (is_string($active)) {
            $active = strtolower(trim($active));
            if (in_array($active, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($active, ['false', '0', 'no', 'off', ''], true)) {
                return false;
            }
        }
        if (is_numeric($active)) {
            return (int)$active !== 0;
        }
        Log::warning('Invalid active state provided to NavLink component', [
            'active' => $active,
            'type' => SecureFileHelper::getType($active),
        ]);
        throw new \InvalidArgumentException('Invalid active state: '.var_export($active, true));
    }

    /**
     * Get the CSS classes for the link with enhanced security.
     *
     * @return string The CSS classes for the navigation link
     */
    protected function getClasses(): string
    {
        if ($this->active) {
            return 'nav-link-active';
        }

        return 'nav-link';
    }

    /**
     * Get the view / contents that represent the component with enhanced security.
     *
     * @return View The rendered view instance
     *
     * @throws \Exception When view rendering fails
     */
    public function render(): View
    {
        try {
            // Validate view exists and is accessible
            if (! file_exists(resource_path('views/components/nav-link.blade.php'))) {
                Log::error('NavLink component view not found', [
                    'view' => 'components.nav-link',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
                throw new \Exception('NavLink component view not found');
            }
            // Render the component with security context
            $view = view('components.nav-link');
            // Add security context to prevent XSS
            $view->with([
                'active' => $this->active,
                'classes' => htmlspecialchars($this->classes, ENT_QUOTES, 'UTF-8'),
                'security_token' => csrf_token(),
            ]);

            return $view;
        } catch (\Exception $e) {
            Log::error('Failed to render NavLink component', [
                'active' => $this->active,
                'classes' => $this->classes,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
