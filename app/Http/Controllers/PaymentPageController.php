<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Payment Page Controller.
 *
 * Renders payment result pages (success, failure, cancel) with input validation.
 */
class PaymentPageController extends Controller
{
    /**
     * Show success page.
     *
     * @param  string  $gateway  Payment gateway identifier
     */
    public function success(string $gateway): View|RedirectResponse
    {
        return $this->renderPage('payment.success', $gateway, 'success');
    }

    /**
     * Show failure page.
     *
     * @param  string  $gateway  Payment gateway identifier
     */
    public function failure(string $gateway): View|RedirectResponse
    {
        return $this->renderPage('payment.failure', $gateway, 'failure');
    }

    /**
     * Show cancel page.
     *
     * @param  string  $gateway  Payment gateway identifier
     */
    public function cancel(string $gateway): View|RedirectResponse
    {
        return $this->renderPage('payment.cancel', $gateway, 'cancel');
    }

    /**
     * Validate gateway and render the given view.
     *
     * @param  string  $view  Blade view path
     * @param  string  $gateway  Payment gateway identifier
     * @param  string  $context  Context for logging
     */
    private function renderPage(string $view, string $gateway, string $context): View|RedirectResponse
    {
        try {
            $sanitizedGateway = htmlspecialchars(trim($gateway), ENT_QUOTES, 'UTF-8');
            if (! preg_match('/^[a-zA-Z0-9_-]+$/', $sanitizedGateway)) {
                Log::warning("Invalid gateway format in {$context} page", [
                    'gateway' => $sanitizedGateway,
                ]);

                return redirect()->route('home')->with('error', 'Invalid payment gateway.');
            }
            /** @var view-string $viewString */
            $viewString = $view;

            return view($viewString, ['gateway' => $sanitizedGateway]);
        } catch (\Exception $e) {
            Log::error("Payment {$context} page error", [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
            ]);

            return redirect()->route('home')->with('error', 'Payment processing error. Please contact support.');
        }
    }
}
