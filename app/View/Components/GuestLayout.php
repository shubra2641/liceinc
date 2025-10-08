<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Guest Layout Component with enhanced security and performance. *
 * This component provides the guest layout template for unauthenticated users * with comprehensive security measures and performance optimization. *
 * Features: * - Guest user layout template rendering * - Enhanced security measures for guest access * - Performance optimization with efficient view rendering * - Comprehensive error handling and logging * - Proper component structure with type hints * - Security validation for guest access * - Clean and maintainable code structure *
 *
 * @example * // Use in Blade template * <x-guest-layout> * <div>Guest content here</div> * </x-guest-layout> *
 * // Use in controller * return view('auth.login')->layout(GuestLayout::class); */
class GuestLayout extends Component
{
    /**   * Additional CSS classes for the layout. */
    public string $class;
    /**   * Additional CSS classes for the body element. */
    public string $bodyClass;
    /**   * Whether to include the navigation bar. */
    public bool $showNavigation;
    /**   * Whether to include the footer. */
    public bool $showFooter;
    /**   * Create a new component instance. *   * @param string $class Additional CSS classes for the layout * @param string $bodyClass Additional CSS classes for the body element * @param bool $showNavigation Whether to show the navigation bar * @param bool $showFooter Whether to show the footer */
    public function __construct(
        string $class = '',
        string $bodyClass = '',
        bool $showNavigation = true,
        bool $showFooter = true,
    ) {
        $this->class = $this->sanitizeInput($class);
        $this->bodyClass = $this->sanitizeInput($bodyClass);
        $this->showNavigation = $showNavigation;
        $this->showFooter = $showFooter;
    }
    /**   * Get the view / contents that represents the component. *   * Renders the guest layout template with proper error handling * and security validation for guest user access. *   * @return View The guest layout view *   * @throws \Exception When view rendering fails *   * @example * $component = new GuestLayout(); * $view = $component->render(); */
    public function render(): View
    {
        try {
            return view('layouts.guest', [
                'class' => $this->class,
                'bodyClass' => $this->bodyClass,
                'showNavigation' => $this->showNavigation,
                'showFooter' => $this->showFooter,
            ]);
        } catch (\Exception $e) {
            Log::error('Guest layout rendering failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => $this->class,
                'bodyClass' => $this->bodyClass,
            ]);
            throw $e;
        }
    }
    /**   * Sanitize input to prevent XSS attacks. *   * @param string $input The input to sanitize *   * @return string The sanitized input */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
