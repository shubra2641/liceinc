<?php
namespace App\Console\Controllers;
use Exception;
use Illuminate\Support\Facades\Log;
/**
 * Console Controller.
 *
 * Handles console commands and scheduled tasks for the application.
 * Provides utility commands for development and maintenance.
 */
class ConsoleController
{
    /**
     * Display an inspiring quote.
     *
     * Shows a random inspiring quote in the console with error handling.
     * Used for development and testing purposes.
     *
     * @return void
     */
    public function inspire()
    {
        try {
            $this->comment(\Illuminate\Foundation\Inspiring::quote());
        } catch (Exception $e) {
            Log::error('Failed to display inspiring quote: '.$e->getMessage());
            $this->error('Failed to display inspiring quote.');
        }
    }
    /**
     * Output comment to console.
     *
     * @param string $message
     *
     * @return void
     */
    protected function comment($message)
    {
        // Console safe output (not HTML). We still escape control chars just in case.
        $safe = is_string($message) ? preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $message) : $message;
        echo $safe.PHP_EOL; // security-ignore: ECHO_NO_HTML_ESCAPE (CLI context)
    }
    /**
     * Output error to console.
     *
     * @param string $message
     *
     * @return void
     */
    protected function error($message)
    {
        $safe = is_string($message) ? preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $message) : $message;
        echo 'Error: '.$safe.PHP_EOL; // security-ignore: ECHO_NO_HTML_ESCAPE (CLI context)
    }
}
