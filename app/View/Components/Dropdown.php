<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Dropdown Component with enhanced security and validation.
 *
 * This component provides a flexible dropdown menu with customizable
 * alignment, width, and styling options with comprehensive validation.
 *
 * Features:
 * - Flexible alignment options (left, right, top)
 * - Customizable width settings
 * - Content class customization
 * - RTL/LTR support
 * - Input validation and sanitization
 * - Type safety and validation
 */
class Dropdown extends Component
{
    /**
     * The alignment of the dropdown menu.
     */
    public string $align;

    /**
     * The width of the dropdown menu.
     */
    public string $width;

    /**
     * The CSS classes for the dropdown content.
     */
    public string $contentClasses;

    /**
     * The computed alignment CSS classes.
     */
    public string $alignmentClasses;

    /**
     * The computed width CSS class.
     */
    public string $widthClass;

    /**
     * Create a new component instance with enhanced validation.
     *
     * @param  string  $align  The alignment of the dropdown (left, right, top)
     * @param  string  $width  The width of the dropdown menu
     * @param  string  $contentClasses  The CSS classes for the dropdown content
     */
    public function __construct(
        string $align = 'right',
        string $width = '48',
        string $contentClasses = 'py-1 bg-white dark:bg-gray-700',
    ) {
        $this->align = $this->validateAlign($align);
        $this->width = $this->validateWidth($width);
        $this->contentClasses = $this->sanitizeContentClasses($contentClasses);
        $this->alignmentClasses = $this->getAlignmentClasses();
        $this->widthClass = $this->getWidthClass();
    }

    /**
     * Get the alignment CSS classes based on the alignment setting.
     *
     * @return string The computed alignment CSS classes
     */
    protected function getAlignmentClasses(): string
    {
        return match ($this->align) {
            'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
            'top' => 'origin-top',
            default => 'ltr:origin-top-right rtl:origin-top-left end-0',
        };
    }

    /**
     * Get the width CSS class based on the width setting.
     *
     * @return string The computed width CSS class
     */
    protected function getWidthClass(): string
    {
        return match ($this->width) {
            '48' => 'w-48',
            default => $this->width,
        };
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View The dropdown component view
     */
    public function render(): View
    {
        return view('components.dropdown');
    }

    /**
     * Validate and sanitize the alignment parameter.
     *
     * @param  string  $align  The alignment value to validate
     *
     * @return string The validated alignment value
     */
    private function validateAlign(string $align): string
    {
        $allowedAlignments = ['left', 'right', 'top'];
        if (! in_array($align, $allowedAlignments)) {
            return 'right'; // Default fallback
        }

        return $align;
    }

    /**
     * Validate and sanitize the width parameter.
     *
     * @param  string  $width  The width value to validate
     *
     * @return string The validated width value
     */
    private function validateWidth(string $width): string
    {
        // Allow numeric values and custom width classes
        if (preg_match('/^[a-zA-Z0-9\-_]+$/', $width)) {
            return $width;
        }

        return '48'; // Default fallback
    }

    /**
     * Sanitize content classes to prevent XSS attacks.
     *
     * @param  string  $contentClasses  The content classes to sanitize
     *
     * @return string The sanitized content classes
     */
    private function sanitizeContentClasses(string $contentClasses): string
    {
        // Remove potentially dangerous characters and keep only safe CSS class characters
        $sanitized = preg_replace('/[^a-zA-Z0-9\s\-_:]/', '', (string)$contentClasses);
        if ($sanitized === null) {
            return '';
        }
        // Trim and normalize whitespace
        $trimmed = trim($sanitized);
        $result = preg_replace('/\s+/', ' ', $trimmed);

        return $result !== null ? $result : '';
    }
}
