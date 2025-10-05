<?php
namespace App\View\Components;
use Illuminate\View\Component;
use Illuminate\View\View;
/**
 * Responsive Navigation Link Component with enhanced security and validation.
 *
 * This component provides a responsive navigation link with active state
 * management and customizable styling with comprehensive validation.
 *
 * Features:
 * - Active state management
 * - Responsive design support
 * - Dark mode compatibility
 * - Customizable CSS classes
 * - Input validation and sanitization
 * - Type safety and validation
 */
class ResponsiveNavLink extends Component
{
    /**
     * Whether the navigation link is in active state.
     */
    public bool $active;
    /**
     * The computed CSS classes for the navigation link.
     */
    public string $classes;
    /**
     * Create a new component instance with enhanced validation.
     *
     * @param  bool  $active  Whether the navigation link is in active state
     */
    public function __construct(bool $active = false)
    {
        $this->active = $this->validateActive($active);
        $this->classes = $this->getClasses();
    }
    /**
     * Get the CSS classes for the navigation link based on active state.
     *
     * @return string The computed CSS classes for the navigation link
     */
    protected function getClasses(): string
    {
        if ($this->active) {
            return 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-600 '.
                'text-start text-base font-medium text-indigo-700 dark:text-indigo-300 '.
                'bg-indigo-50 dark:bg-indigo-900/50 focus:outline-none focus:text-indigo-800 '.
                'dark:focus:text-indigo-200 focus:bg-indigo-100 dark:focus:bg-indigo-900 '.
                'focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out';
        }
        return 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start '.
            'text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 '.
            'dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 '.
            'hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none '.
            'focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 '.
            'dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 '.
            'transition duration-150 ease-in-out';
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return View The responsive navigation link component view
     */
    public function render(): View
    {
        return view('components.responsive-nav-link');
    }
    /**
     * Validate and sanitize the active parameter.
     *
     * @param  mixed  $active  The active value to validate
     *
     * @return bool The validated active state
     */
    private function validateActive($active): bool
    {
        // Convert various truthy/falsy values to boolean
        if (is_bool($active)) {
            return $active;
        }
        if (is_string($active)) {
            $active = strtolower(trim($active));
            return in_array($active, ['true', '1', 'yes', 'on', 'active']);
        }
        if (is_numeric($active)) {
            return (bool)$active;
        }
        // Default to false for any other type
        return false;
    }
}
