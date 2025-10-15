<?php

declare(strict_types=1);

namespace App\Console\Controllers;

use Exception;
use Illuminate\Support\Facades\Log;

class ConsoleController
{
    public function inspire(): void
    {
        try {
            $this->output(\Illuminate\Foundation\Inspiring::quote());
        } catch (Exception $e) {
            Log::error('Failed to display inspiring quote: ' . $e->getMessage());
            $this->output('Failed to display inspiring quote.', 'Error: ');
        }
    }

    protected function comment(string $message): void
    {
        $this->output($message);
    }

    protected function error(string $message): void
    {
        $this->output($message, 'Error: ');
    }

    private function output(string $message, string $prefix = ''): void
    {
        $safe = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $message);
        echo $prefix . $safe . PHP_EOL;
    }
}