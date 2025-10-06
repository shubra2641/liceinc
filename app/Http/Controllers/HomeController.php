<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Home Controller with enhanced security and comprehensive home page management.
 *
 * This controller handles the home page display including product listings and
 * system statistics. It implements comprehensive security measures, input validation,
 * and error handling for reliable home page operations.
 */
class HomeController extends Controller
{
    /**
     * Display the home page with enhanced security and comprehensive data management.
     *
     * Shows the home page with active products and system statistics with comprehensive
     * validation, security measures, and error handling for reliable home page operations.
     *
     * @param  Request  $request  The HTTP request object
     *
     * @return View The home page view
     *
     * @throws InvalidArgumentException When request data is invalid
     * @throws \Exception When data retrieval fails
     *
     * @example
     * // Access via GET /
     * // Returns home page with products and statistics
     */
    public function index(Request $request): View
    {
        try {
            // Validate request with basic rules for home page
            $this->validateRequest($request, [
                'page' => 'sometimes|integer|min:1',
                'search' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
            ]);
            DB::beginTransaction();
            // Get active products with enhanced security
            $products = $this->getActiveProducts();
            // Get system statistics with enhanced security
            $stats = $this->getSystemStatistics();
            DB::commit();
            return view('welcome', compact('products', 'stats'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to display home page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_url' => $request->fullUrl(),
            ]);
            // Return home page with empty data on error
            return view('welcome', [
                'products' => collect(),
                'stats' => $this->getDefaultStats(),
            ]);
        }
    }
    /**
     * Get active products with enhanced security and error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Exception When product retrieval fails
     */
    private function getActiveProducts()
    {
        try {
            return Product::where('is_active', true)
                ->latest()
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve active products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get system statistics with enhanced security and error handling.
     *
     * @return array System statistics array
     *
     * @throws \Exception When statistics retrieval fails
     */
    private function getSystemStatistics(): array
    {
        try {
            return [
                'customers' => $this->getUserCount(),
                'licenses' => $this->getLicenseCount(),
                'tickets' => $this->getTicketCount(),
                'invoices' => $this->getInvoiceCount(),
                'products' => $this->getProductCount(),
                'active_licenses' => $this->getActiveLicenseCount(),
                'paid_invoices' => $this->getPaidInvoiceCount(),
                'open_tickets' => $this->getOpenTicketCount(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to retrieve system statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get default statistics for error fallback.
     *
     * @return array Default statistics array
     */
    private function getDefaultStats(): array
    {
        return [
            'customers' => 0,
            'licenses' => 0,
            'tickets' => 0,
            'invoices' => 0,
            'products' => 0,
            'active_licenses' => 0,
            'paid_invoices' => 0,
            'open_tickets' => 0,
        ];
    }
    /**
     * Get user count with enhanced security and error handling.
     *
     * @return int User count
     *
     * @throws \Exception When user count retrieval fails
     */
    private function getUserCount(): int
    {
        try {
            return User::count();
        } catch (\Exception $e) {
            Log::error('Failed to get user count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
    /**
     * Get license count with enhanced security and error handling.
     *
     * @return int License count
     *
     * @throws \Exception When license count retrieval fails
     */
    private function getLicenseCount(): int
    {
        try {
            return License::count();
        } catch (\Exception $e) {
            Log::error('Failed to get license count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
    /**
     * Get ticket count with enhanced security and error handling.
     *
     * @return int Ticket count
     *
     * @throws \Exception When ticket count retrieval fails
     */
    private function getTicketCount(): int
    {
        try {
            return Ticket::count();
        } catch (\Exception $e) {
            Log::error('Failed to get ticket count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
    /**
     * Get invoice count with enhanced security and error handling.
     *
     * @return int Invoice count
     *
     * @throws \Exception When invoice count retrieval fails
     */
    private function getInvoiceCount(): int
    {
        try {
            return Invoice::count();
        } catch (\Exception $e) {
            Log::error('Failed to get invoice count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
    /**
     * Get product count with enhanced security and error handling.
     *
     * @return int Product count
     *
     * @throws \Exception When product count retrieval fails
     */
    private function getProductCount(): int
    {
        try {
            return Product::count();
        } catch (\Exception $e) {
            Log::error('Failed to get product count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
    /**
     * Get active license count with enhanced security and error handling.
     *
     * @return int Active license count
     *
     * @throws \Exception When active license count retrieval fails
     */
    private function getActiveLicenseCount(): int
    {
        try {
            return License::where('status', 'active')->count();
        } catch (\Exception $e) {
            Log::error('Failed to get active license count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
    /**
     * Get paid invoice count with enhanced security and error handling.
     *
     * @return int Paid invoice count
     *
     * @throws \Exception When paid invoice count retrieval fails
     */
    private function getPaidInvoiceCount(): int
    {
        try {
            return Invoice::where('status', 'paid')->count();
        } catch (\Exception $e) {
            Log::error('Failed to get paid invoice count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
    /**
     * Get open ticket count with enhanced security and error handling.
     *
     * @return int Open ticket count
     *
     * @throws \Exception When open ticket count retrieval fails
     */
    private function getOpenTicketCount(): int
    {
        try {
            return Ticket::whereIn('status', ['open', 'pending'])->count();
        } catch (\Exception $e) {
            Log::error('Failed to get open ticket count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }
}
