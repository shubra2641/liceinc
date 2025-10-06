<?php

namespace App\Helpers;

/**
 * Security Helper
 * 
 * Provides secure output escaping and XSS protection
 */
class SecurityHelper
{
    /**
     * Escape output to prevent XSS
     */
    public static function escapeOutput(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for translation strings
     */
    public static function escapeTranslation(string $translation): string
    {
        return htmlspecialchars($translation, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for exception messages
     */
    public static function escapeException(string $message): string
    {
        return htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for variable interpolation
     */
    public static function escapeVariable(string $variable): string
    {
        return htmlspecialchars($variable, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
