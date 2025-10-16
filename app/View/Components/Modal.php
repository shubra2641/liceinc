<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Modal Component with enhanced security.
 *
 * A flexible modal component that provides customizable modal dialogs
 * with comprehensive security measures and proper validation.
 *
 * Features:
 * - Customizable modal dialogs with different sizes
 * - Dynamic show/hide functionality
 * - Enhanced security measures (input validation, XSS protection)
 * - Comprehensive error handling for invalid inputs
 * - Proper type hints and return types
 * - Clean code structure with no duplicate patterns
 * - Responsive design with Tailwind CSS classes
 */
class Modal extends Component
{
    /**
     * The modal name/identifier.
     */
    public string $name;

    /**
     * Whether the modal should be shown.
     */
    public bool $show;

    /**
     * The maximum width of the modal.
     */
    public string $maxWidth;

    /**
     * The CSS class for maximum width.
     */
    public string $maxWidthClass;

    /**
     * Create a new component instance with enhanced security.
     *
     * Initializes the modal component with proper validation
     * and security measures.
     *
     * @param  string  $name  The modal name/identifier
     * @param  bool  $show  Whether the modal should be shown
     * @param  string  $maxWidth  The maximum width of the modal
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     */
    public function __construct(string $name, bool $show = false, string $maxWidth = '2xl')
    {
        // Validate and sanitize inputs
        if (empty($name)) {
            throw new \InvalidArgumentException('Modal name cannot be empty');
        }
        $this->name = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
        $this->show = (bool)$show;
        $this->maxWidth = $this->validateMaxWidth($maxWidth);
        $this->maxWidthClass = $this->getMaxWidthClass();
    }

    /**
     * Get the max width CSS class with enhanced security.
     *
     * Returns the appropriate CSS class for the modal's maximum width
     * with proper validation and security measures.
     *
     * @return string The CSS class for maximum width
     *
     * @version 1.0.6
     */
    protected function getMaxWidthClass(): string
    {
        $maxWidthClasses = [
            'sm' => 'sm:max-w-sm',
            'md' => 'sm:max-w-md',
            'lg' => 'sm:max-w-lg',
            'xl' => 'sm:max-w-xl',
            '2xl' => 'sm:max-w-2xl',
            '3xl' => 'sm:max-w-3xl',
            '4xl' => 'sm:max-w-4xl',
            '5xl' => 'sm:max-w-5xl',
            '6xl' => 'sm:max-w-6xl',
            '7xl' => 'sm:max-w-7xl',
        ];

        return $maxWidthClasses[$this->maxWidth] ?? 'sm:max-w-2xl';
    }

    /**
     * Get the view / contents that represent the component with enhanced security.
     *
     * Returns the view for the modal component with proper
     * security measures and validation.
     *
     * @return View The view for the modal component
     *
     * @version 1.0.6
     */
    public function render(): View
    {
        return view('components.modal', [
            'name' => $this->name,
            'show' => $this->show,
            'maxWidth' => $this->maxWidth,
            'maxWidthClass' => $this->maxWidthClass,
        ]);
    }

    /**
     * Validate and sanitize the maximum width parameter.
     *
     * Validates the maximum width parameter and returns a sanitized
     * version with proper security measures.
     *
     * @param  string  $maxWidth  The maximum width to validate
     *
     * @return string The validated and sanitized maximum width
     *
     * @throws \InvalidArgumentException When max width is invalid
     *
     * @version 1.0.6
     */
    private function validateMaxWidth(string $maxWidth): string
    {
        $allowedSizes = ['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'];
        $sanitizedMaxWidth = htmlspecialchars(trim($maxWidth), ENT_QUOTES, 'UTF-8');
        if (! in_array($sanitizedMaxWidth, $allowedSizes, true)) {
            throw new \InvalidArgumentException(
                'Invalid max width. Allowed values: ' . implode(', ', $allowedSizes),
            );
        }

        return $sanitizedMaxWidth;
    }
}
