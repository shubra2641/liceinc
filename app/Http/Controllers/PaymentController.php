<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Services\Email\EmailFacade;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Payment Controller - Simplified
 * 
 * Handles payment processing, gateway management, and transaction handling.
 * Supports PayPal, Stripe, and custom invoice payments.
 */
class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private EmailFacade $emailService
    ) {}

    /**
     * Show payment gateway selection page
     * 
     * @param Product $product The product to purchase
     * @return View|RedirectResponse
     */
    public function showPaymentGateways(Product $product): View|RedirectResponse
    {
        try {
            // Rate limiting
            if ($this->isRateLimited('payment-gateways')) {
                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }

            // Check authentication
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', trans('app.Please login to purchase this product'));
            }

            // Check product availability
            if (!$product->is_active || $product->price <= 0) {
                return redirect()->back()->with('error', trans('app.Product is not available for purchase'));
            }

            // Get enabled gateways
            $enabledGateways = PaymentSetting::getEnabledGateways();
            if (empty($enabledGateways)) {
                return redirect()->back()->with('error', trans('app.No payment gateways are currently available'));
            }

            return view('payment.gateways', [
                'product' => $product,
                'enabledGateways' => $enabledGateways
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load payment gateways', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Failed to load payment options. Please try again.');
        }
    }

    /**
     * Process payment with selected gateway
     * 
     * @param Request $request The HTTP request
     * @param Product $product The product being purchased
     * @return RedirectResponse
     */
    public function processPayment(Request $request, Product $product): RedirectResponse
    {
        try {
            // Rate limiting
            if ($this->isRateLimited('payment-process')) {
                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }

            // Validate request
            $validated = $request->validate([
                'gateway' => 'required|in:paypal,stripe',
                'invoice_id' => 'nullable|exists:invoices,id',
            ]);

            // Check authentication
            if (!Auth::check()) {
                return redirect()->back()->with('error', trans('app.Please login to purchase this product'));
            }

            // Check gateway availability
            if (!PaymentSetting::isGatewayEnabled($validated['gateway'])) {
                return redirect()->back()->with('error', trans('app.Selected payment gateway is not available'));
            }

            DB::beginTransaction();

            // Handle existing invoice
            $invoice = null;
            if ($validated['invoice_id']) {
                $invoice = Invoice::where('id', $validated['invoice_id'])
                    ->where('user_id', Auth::id())
                    ->where('status', 'pending')
                    ->first();

                if (!$invoice) {
                    DB::rollBack();
                    return redirect()->back()->with('error', trans('app.Invoice not found or already paid'));
                }
            }

            // Create order data
            $orderData = [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'amount' => $invoice ? $invoice->amount : $product->price,
                'currency' => 'USD',
                'payment_gateway' => $validated['gateway'],
                'payment_status' => 'pending',
                'invoice_id' => $invoice ? $invoice->id : null,
            ];

            // Store session data
            session(['payment_product_id' => $product->id]);
            if ($invoice) {
                session(['payment_invoice_id' => $invoice->id]);
            }

            // Process payment
            $paymentResult = $this->paymentService->processPayment($orderData, $validated['gateway']);

            // Store session ID for Stripe
            if ($validated['gateway'] === 'stripe' && isset($paymentResult['session_id'])) {
                session(['stripe_session_id' => $paymentResult['session_id']]);
            }

            DB::commit();

            return redirect()->to($paymentResult['redirect_url'] ?? '/');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', trans('app.Payment processing failed. Please try again.'));
        }
    }

    /**
     * Handle successful payment callback
     * 
     * @param Request $request The HTTP request
     * @param string $gateway The payment gateway name
     * @return RedirectResponse
     */
    public function handleSuccess(Request $request, string $gateway): RedirectResponse
    {
        try {
            // Rate limiting
            if ($this->isRateLimited('payment-success')) {
                return redirect()->route('user.dashboard')->with('error', 'Too many requests. Please try again later.');
            }

            // Get transaction ID
            $transactionId = $this->getTransactionId($request, $gateway);
            if (!$transactionId) {
                return redirect()->route('user.dashboard')->with('error', trans('app.Invalid payment response'));
            }

            // Verify payment
            $verificationResult = $this->paymentService->verifyPayment($gateway, $transactionId);
            if (!$verificationResult['success']) {
                return redirect()->route('payment.failure-page', $gateway)
                    ->with('error_message', trans('app.Payment verification failed. Please contact support.'));
            }

            // Handle custom invoice payment
            if (session('payment_is_custom', false) && session('payment_invoice_id')) {
                return $this->handleCustomInvoicePayment($transactionId, $gateway);
            }

            // Handle product purchase
            return $this->handleProductPurchase($transactionId, $gateway);
        } catch (\Exception $e) {
            Log::error('Payment success handling failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('payment.failure-page', $gateway)
                ->with('error_message', trans('app.Payment processing error. Please contact support.'));
        }
    }

    /**
     * Handle cancelled payment
     * 
     * @param Request $request The HTTP request
     * @param string $gateway The payment gateway name
     * @return RedirectResponse
     */
    public function handleCancel(Request $request, string $gateway): RedirectResponse
    {
        try {
            // Rate limiting
            if ($this->isRateLimited('payment-cancel')) {
                return redirect()->route('user.dashboard')->with('error', 'Too many requests. Please try again later.');
            }

            // Clear session
            session()->forget('payment_product_id');
            return redirect()->route('payment.cancel', $gateway)
                ->with('info', trans('app.Payment was cancelled. You can try again anytime.'));
        } catch (\Exception $e) {
            Log::error('Payment cancellation handling failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
            ]);
            return redirect()->route('user.dashboard')->with('error', 'Payment cancellation failed. Please try again.');
        }
    }

    /**
     * Handle payment failure
     * 
     * @param Request $request The HTTP request
     * @param string $gateway The payment gateway name
     * @return RedirectResponse
     */
    public function handleFailure(Request $request, string $gateway): RedirectResponse
    {
        try {
            // Rate limiting
            if ($this->isRateLimited('payment-failure')) {
                return redirect()->route('user.dashboard')->with('error', 'Too many requests. Please try again later.');
            }

            // Clear session
            session()->forget('payment_product_id');
            $error = $this->sanitizeInput($request->get('error', 'Unknown error'));

            Log::warning('Payment failure', [
                'gateway' => $gateway,
                'error' => $error,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('payment.failure', $gateway)
                ->with('error_message', trans('app.Payment failed: :error', ['error' => $error]));
        } catch (\Exception $e) {
            Log::error('Payment failure handling failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
            ]);
            return redirect()->route('user.dashboard')->with('error', 'Payment failure handling failed. Please try again.');
        }
    }

    /**
     * Handle webhook notifications from payment gateways
     * 
     * @param Request $request The HTTP request
     * @param string $gateway The payment gateway name
     * @return JsonResponse
     */
    public function handleWebhook(Request $request, string $gateway): JsonResponse
    {
        try {
            // Rate limiting
            $key = 'payment-webhook:' . request()->ip();
            if (RateLimiter::tooManyAttempts($key, 100)) {
                return response()->json(['status' => 'error', 'message' => 'Rate limit exceeded'], 429);
            }
            RateLimiter::hit($key, 60);

            $serviceRequest = new \App\Services\Request($request->all());
            $result = $this->paymentService->handleWebhook($serviceRequest, $gateway);

            if ($result['success']) {
                return response()->json(['status' => 'success']);
            } else {
                Log::warning('Webhook processing failed', [
                    'gateway' => $gateway,
                    'message' => $result['message'],
                ]);
                return response()->json(['status' => 'error', 'message' => $result['message']], 400);
            }
        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
            ]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Process custom invoice payment
     * 
     * @param Request $request The HTTP request
     * @param Invoice $invoice The custom invoice to pay
     * @return RedirectResponse
     */
    public function processCustomPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        try {
            // Rate limiting
            if ($this->isRateLimited('payment-custom')) {
                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }

            // Validate request
            $validated = $request->validate([
                'gateway' => 'required|in:stripe,paypal',
            ]);

            // Check gateway availability
            if (!PaymentSetting::isGatewayEnabled($validated['gateway'])) {
                return redirect()->back()->with('error', 'Payment gateway is not available');
            }

            // Check authorization
            if ($invoice->user_id !== Auth::id()) {
                abort(403);
            }

            // Check invoice status
            if ($invoice->status !== 'pending') {
                return redirect()->back()->with('error', 'Invoice is not available for payment');
            }

            // Create order data
            $orderData = [
                'user_id' => $invoice->user_id,
                'product_id' => null,
                'amount' => $invoice->amount,
                'currency' => $invoice->currency,
                'invoice_id' => $invoice->id,
                'is_custom' => true,
            ];

            // Store session data
            session(['payment_invoice_id' => $invoice->id]);
            session(['payment_is_custom' => true]);

            // Process payment
            $result = $this->paymentService->processPayment($orderData, $validated['gateway']);

            if ($result['success']) {
                return redirect()->to($result['redirect_url'] ?? '/');
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Custom payment processing failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);
            return redirect()->back()->with('error', 'Payment processing failed');
        }
    }

    /**
     * Check if request is rate limited
     * 
     * @param string $key The rate limit key
     * @return bool
     */
    private function isRateLimited(string $key): bool
    {
        $fullKey = $key . ':' . (Auth::id() ?? request()->ip());
        if (RateLimiter::tooManyAttempts($fullKey, 10)) {
            Log::warning('Rate limit exceeded', [
                'key' => $key,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return true;
        }
        RateLimiter::hit($fullKey, 300);
        return false;
    }

    /**
     * Get transaction ID from request
     * 
     * @param Request $request The HTTP request
     * @param string $gateway The payment gateway name
     * @return string|null
     */
    private function getTransactionId(Request $request, string $gateway): ?string
    {
        if ($gateway === 'stripe') {
            return $request->get('session_id') ?? session('stripe_session_id');
        } else {
            return $request->get('paymentId') ?? $request->get('payment_intent') ?? $request->get('token');
        }
    }

    /**
     * Handle custom invoice payment
     * 
     * @param string $transactionId The transaction ID
     * @param string $gateway The payment gateway name
     * @return RedirectResponse
     */
    private function handleCustomInvoicePayment(string $transactionId, string $gateway): RedirectResponse
    {
        $invoiceId = session('payment_invoice_id');
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            Log::error('Custom invoice not found', [
                'invoice_id' => $invoiceId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('user.dashboard')->with('error', trans('app.Invoice not found'));
        }

        DB::beginTransaction();
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'metadata' => array_merge($invoice->metadata ?? [], [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
            ]),
        ]);
        DB::commit();

        // Send emails
        try {
            $this->emailService->sendCustomInvoicePaymentConfirmation($invoice);
            $this->emailService->sendAdminCustomInvoicePaymentNotification($invoice);
        } catch (\Exception $e) {
            Log::error('Failed to send custom invoice payment emails', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId,
            ]);
        }

        // Clear session
        session()->forget(['payment_invoice_id', 'payment_is_custom']);

        return redirect()->route('payment.success-page', $gateway)
            ->with('success', trans('app.Payment successful! Your service payment has been processed.'))
            ->with('invoice', $invoice);
    }

    /**
     * Handle product purchase
     * 
     * @param string $transactionId The transaction ID
     * @param string $gateway The payment gateway name
     * @return RedirectResponse
     */
    private function handleProductPurchase(string $transactionId, string $gateway): RedirectResponse
    {
        $productId = session('payment_product_id');
        $product = $productId ? Product::find($productId) : Product::where('is_active', true)->where('price', '>', 0)->first();

        if (!$product) {
            Log::error('No products available for purchase', [
                'product_id' => $productId,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('user.dashboard')->with('error', trans('app.No products available for purchase'));
        }

        $invoiceId = session('payment_invoice_id');
        $existingInvoice = $invoiceId ? Invoice::find($invoiceId) : null;

        // Create order data
        $orderData = [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'amount' => $existingInvoice ? $existingInvoice->amount : $product->price,
            'currency' => 'USD',
            'payment_gateway' => $gateway,
            'payment_status' => 'paid',
            'invoice_id' => $invoiceId,
        ];

        $result = $this->paymentService->createLicenseAndInvoice($orderData, $gateway, $transactionId);

        if ($result['success']) {
            // Clear session
            session()->forget(['payment_product_id', 'payment_invoice_id']);

            // Send emails
            try {
                if ($result['license'] instanceof \App\Models\License && $result['invoice'] instanceof \App\Models\Invoice) {
                    $this->emailService->sendPaymentConfirmation($result['license'], $result['invoice']);
                    $this->emailService->sendLicenseCreated($result['license']);
                    $this->emailService->sendAdminPaymentNotification($result['license'], $result['invoice']);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send payment emails', [
                    'error' => $e->getMessage(),
                    'license_id' => $result['license']->id ?? 'N/A',
                    'invoice_id' => $result['invoice']->id ?? 'N/A',
                ]);
            }

            return redirect()->route('payment.success-page', $gateway)
                ->with('license', $result['license'])
                ->with('invoice', $result['invoice'])
                ->with('success', trans('app.Payment successful! Your license has been activated.'));
        } else {
            return redirect()->route('payment.failure-page', $gateway)
                ->with('error_message', trans('app.Payment successful but failed to create license. Please contact support.'));
        }
    }

    /**
     * Sanitize input data
     * 
     * @param string|null $input The input to sanitize
     * @return string
     */
    private function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}