<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\License;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Services\EmailService;
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
 * Payment Controller with enhanced security.
 *
 * This controller handles payment processing, gateway management, and transaction
 * handling with comprehensive security measures and proper error handling.
 *
 * Features:
 * - Payment gateway selection and processing
 * - Payment success and failure handling
 * - Webhook processing for payment gateways
 * - Custom invoice payment processing
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling and logging
 * - Proper logging for errors and warnings only
 * - Rate limiting for payment operations
 * - Authorization checks for payment access
 */
class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected EmailService $emailService;
    /**
     * Create a new controller instance.
     *
     * @param  PaymentService  $paymentService  The payment service instance
     * @param  EmailService  $emailService  The email service instance
     *
     * @return void
     */
    public function __construct(
        PaymentService $paymentService,
        EmailService $emailService,
    ) {
        $this->paymentService = $paymentService;
        $this->emailService = $emailService;
    }
    /**
     * Show payment gateway selection page with enhanced security.
     *
     * Displays available payment gateways for product purchase with
     * proper authentication and product validation.
     *
     * @param  Product  $product  The product to purchase
     *
     * @return View|RedirectResponse The payment gateways view or redirect
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access: GET /payment/gateways/{product}
     * // Returns: View with available payment gateways
     */
    public function showPaymentGateways(Product $product): View|RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'payment-gateways:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for payment gateways', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Check if user is authenticated
            if (! Auth::check()) {
                Log::warning('Unauthenticated access attempt to payment gateways', [
                    'productId' => $product->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->route('login')->with('error', trans('app.Please login to purchase this product'));
            }
            // Check if product is active and has a price
            if (! $product->isActive || $product->price <= 0) {
                Log::warning('Invalid product access attempt', [
                    'productId' => $product->id,
                    'isActive' => $product->isActive,
                    'price' => $product->price,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                return redirect()->back()->with('error', trans('app.Product is not available for purchase'));
            }
            // Get enabled payment gateways
            $enabledGateways = PaymentSetting::getEnabledGateways();
            if (empty($enabledGateways)) {
                Log::warning('No payment gateways available', [
                    'productId' => $product->id,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                return redirect()->back()->with('error', trans('app.No payment gateways are currently available'));
            }
            return view('payment.gateways', ['product' => $product, 'enabledGateways' => $enabledGateways]);
        } catch (\Exception $e) {
            Log::error('Failed to load payment gateways', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'productId' => $product->id,
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->back()->with('error', 'Failed to load payment options. Please try again.');
        }
    }
    /**
     * Process payment with selected gateway with enhanced security.
     *
     * Processes payment through the selected gateway with comprehensive
     * validation and security measures.
     *
     * @param  Request  $request  The HTTP request containing payment data
     * @param  Product  $product  The product being purchased
     *
     * @return RedirectResponse Redirect to payment gateway or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /payment/process/{product}
     * {
     *     "gateway": "paypal",
     *     "invoice_id": 123
     * }
     */
    public function processPayment(Request $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'payment-process:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 5)) {
                Log::warning('Rate limit exceeded for payment processing', [
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            $validated = $request->validate([
                'gateway' => 'required|in:paypal, stripe',
                'invoice_id' => 'nullable|exists:invoices, id',
            ]);
            $validatedArray = is_array($validated) ? $validated : [];
            if (! Auth::check()) {
                Log::warning('Unauthenticated payment processing attempt', [
                    'productId' => $product->id,
                    'gateway' => $validatedArray['gateway'] ?? 'unknown',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->back()->with('error', trans('app.Please login to purchase this product'));
            }
            // Check if gateway is enabled
            $gateway = is_string($validatedArray['gateway'] ?? null) ? $validatedArray['gateway'] : '';
            if (! PaymentSetting::isGatewayEnabled($gateway)) {
                Log::warning('Disabled gateway access attempt', [
                    'gateway' => $gateway,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                return redirect()->back()->with('error', trans('app.Selected payment gateway is not available'));
            }
            DB::beginTransaction();
            // Check if this is for an existing invoice
            $invoice = null;
            $invoiceId = $validatedArray['invoice_id'] ?? null;
            if (isset($invoiceId) && $invoiceId) {
                $invoice = Invoice::where('id', $invoiceId)
                    ->where('userId', Auth::id())
                    ->where('status', 'pending')
                    ->first();
                if (! $invoice) {
                    DB::rollBack();
                    Log::warning('Invalid invoice access attempt', [
                        'invoice_id' => $invoiceId,
                        'userId' => Auth::id(),
                        'ip' => request()->ip(),
                    ]);
                    return redirect()->back()->with('error', trans('app.Invoice not found or already paid'));
                }
            }
            // Create temporary order data for payment processing
            $orderData = [
                'userId' => Auth::id(),
                'productId' => $product->id,
                'amount' => $invoice ? $invoice->amount : $product->price,
                'currency' => 'USD',
                'payment_gateway' => $gateway,
                'payment_status' => 'pending',
                'invoice_id' => $invoice ? $invoice->id : null,
            ];
            // Store product info in session for success callback
            session(['payment_productId' => $product->id]);
            if ($invoice) {
                session(['payment_invoice_id' => $invoice->id]);
            }
            // Process payment based on gateway
            $paymentResult = $this->paymentService->processPayment($orderData, $gateway);
            // Store session_id for Stripe
            if ($gateway === 'stripe' && isset($paymentResult['session_id'])) {
                session(['stripe_session_id' => $paymentResult['session_id']]);
            }
            DB::commit();
            // Redirect to payment gateway
            return redirect()->to(
                is_string($paymentResult['redirect_url'] ?? null)
                    ? $paymentResult['redirect_url']
                    : '/'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Payment processing validation failed', [
                'errors' => $e->errors(),
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->back()->with('error', trans('app.Payment processing failed. Please try again.'));
        }
    }
    /**
     * Handle successful payment callback with enhanced security.
     *
     * Processes successful payment callbacks from payment gateways
     * with comprehensive validation and security measures.
     *
     * @param  Request  $request  The HTTP request from payment gateway
     * @param  string  $gateway  The payment gateway name
     *
     * @return RedirectResponse Redirect to success or failure page
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Callback from PayPal/Stripe:
     * GET /payment/success/{gateway}
     * // Returns: Redirect to success page with license data
     */
    public function handleSuccess(Request $request, string $gateway): RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'payment-success:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for payment success callback', [
                    'gateway' => $gateway,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->route('user.dashboard')->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Log PayPal payments only
            if ($gateway === 'paypal') {
                $requestData = $request->all();
                /**
 * @var array<string, mixed> $sanitizedData
*/
                $sanitizedData = $requestData;
                Log::warning('PayPal Payment Success Callback', [
                    'gateway' => $gateway,
                    'all_params' => $this->sanitizeLogData($sanitizedData),
                    'userId' => Auth::id(),
                    'session_data' => [
                        'payment_productId' => session('payment_productId'),
                        'payment_invoice_id' => session('payment_invoice_id'),
                    ],
                ]);
            }
            // Get transaction ID based on gateway
            if ($gateway === 'stripe') {
                $transactionId = $request->get('session_id') ?? session('stripe_session_id');
            } else {
                $transactionId = $request->get('paymentId') ?? $request->get('payment_intent')
                    ?? $request->get('token');
            }
            if ($gateway === 'paypal') {
                Log::warning('PayPal Transaction ID found', ['transaction_id' => $transactionId]);
            }
            if (! $transactionId) {
                if ($gateway === 'paypal') {
                    Log::error('No PayPal transaction ID found in request', [
                        'gateway' => $gateway,
                        'userId' => Auth::id(),
                        'ip' => request()->ip(),
                    ]);
                }
                return redirect()->route('user.dashboard')->with(
                    'error',
                    trans('app.Invalid payment response')
                );
            }
            $user = Auth::user();
            // Verify payment with gateway
            $verificationResult = $this->paymentService->verifyPayment(
                $gateway,
                is_string($transactionId) ? $transactionId : ''
            );
            if ($verificationResult['success']) {
                // Check if this is a custom invoice payment
                $isCustom = session('payment_is_custom', false);
                $invoiceId = session('payment_invoice_id');
                if ($isCustom && $invoiceId) {
                    // Handle custom invoice payment
                    $existingInvoice = Invoice::find($invoiceId);
                    if (! $existingInvoice || $existingInvoice instanceof \Illuminate\Database\Eloquent\Collection) {
                        Log::error('Custom invoice not found', [
                            'invoice_id' => $invoiceId,
                            'userId' => Auth::id(),
                            'ip' => request()->ip(),
                        ]);
                        return redirect()->route('user.dashboard')->with('error', trans('app.Invoice not found'));
                    }
                    DB::beginTransaction();
                    // Update existing custom invoice
                    $existingInvoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'metadata' => array_merge($existingInvoice->metadata ?? [], [
                            'gateway' => $gateway,
                            'transaction_id' => $transactionId,
                        ]),
                    ]);
                    DB::commit();
                    // Send emails for custom invoice
                    try {
                        $this->emailService->sendCustomInvoicePaymentConfirmation($existingInvoice);
                        $this->emailService->sendAdminCustomInvoicePaymentNotification($existingInvoice);
                    } catch (\Exception $e) {
                        Log::error('Failed to send custom invoice payment emails', [
                            'error' => $e->getMessage(),
                            'invoice_id' => $invoiceId,
                            'userId' => Auth::id(),
                        ]);
                    }
                    // Clear session
                    session()->forget(['payment_invoice_id', 'payment_is_custom']);
                    return redirect()->route('payment.success-page', $gateway)
                        ->with('success', trans('app.Payment successful! Your service payment has been processed.'))
                        ->with('invoice', $existingInvoice);
                } else {
                    // Handle product purchase
                    $productId = session('payment_productId');
                    $product = $productId
                        ? Product::find($productId)
                        : Product::where('isActive', true)->where('price', '>', 0)->first();
                    if (! $product) {
                        Log::error('No products available for purchase', [
                            'productId' => $productId,
                            'userId' => Auth::id(),
                            'ip' => request()->ip(),
                        ]);
                        return redirect()->route('user.dashboard')->with(
                            'error',
                            trans('app.No products available for purchase'),
                        );
                    }
                    $existingInvoice = null;
                    if ($invoiceId) {
                        $existingInvoice = Invoice::find($invoiceId);
                    }
                    // Create order data
                    $orderData = [
                        'userId' => $user?->id,
                        'productId' => $product->id,
                        'amount' => $existingInvoice ? $existingInvoice->amount : $product->price,
                        'currency' => 'USD',
                        'payment_gateway' => $gateway,
                        'payment_status' => 'paid',
                        'invoice_id' => $invoiceId,
                    ];
                    Log::warning('Creating license and invoice', [
                        'orderData' => $this->sanitizeLogData($orderData),
                        'gateway' => $gateway,
                        'transactionId' => $transactionId,
                    ]);
                    $result = $this->paymentService->createLicenseAndInvoice(
                        $orderData,
                        $gateway,
                        is_string($transactionId) ? $transactionId : null
                    );
                    Log::warning('License and invoice creation result', [
                        'success' => $result['success'] ?? false,
                        'licenseId' => (is_object($result['license'] ?? null)
                            && isset($result['license']->id))
                            ? $result['license']->id
                            : 'N/A',
                        'invoice_id' => (is_object($result['invoice'] ?? null)
                            && isset($result['invoice']->id))
                            ? $result['invoice']->id
                            : 'N/A',
                    ]);
                    if ($result['success']) {
                        // Clear payment session
                        session()->forget(['payment_productId', 'payment_invoice_id']);
                        // Send emails
                        try {
                            if (
                                $result['license'] instanceof \App\Models\License
                                && $result['invoice'] instanceof \App\Models\Invoice
                            ) {
                                $this->emailService->sendPaymentConfirmation($result['license'], $result['invoice']);
                                $this->emailService->sendLicenseCreated($result['license']);
                                $this->emailService->sendAdminPaymentNotification(
                                    $result['license'],
                                    $result['invoice']
                                );
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to send payment emails', [
                                'error' => $e->getMessage(),
                                'licenseId' => $result['license']->id ?? 'N/A',
                                'invoice_id' => $result['invoice']->id ?? 'N/A',
                            ]);
                        }
                        return redirect()->route('payment.success-page', $gateway)
                            ->with('license', $result['license'])
                            ->with('invoice', $result['invoice'])
                            ->with('success', trans('app.Payment successful! Your license has been activated.'));
                    } else {
                        return redirect()->route('payment.failure-page', $gateway)
                            ->with(
                                'error_message',
                                trans('app.Payment successful but failed to create license. Please contact support.'),
                            );
                    }
                }
            } else {
                return redirect()->route('payment.failure-page', $gateway)
                    ->with('error_message', trans('app.Payment verification failed. Please contact support.'));
            }
        } catch (\Exception $e) {
            Log::error('Payment success handling failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'gateway' => $gateway,
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->route('payment.failure-page', $gateway)
                ->with('error_message', trans('app.Payment processing error. Please contact support.'));
        }
    }
    /**
     * Handle cancelled payment with enhanced security.
     *
     * Processes cancelled payment requests with proper session cleanup
     * and user notification.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $gateway  The payment gateway name
     *
     * @return RedirectResponse Redirect to cancel page
     *
     * @example
     * // User cancels payment:
     * GET /payment/cancel/{gateway}
     * // Returns: Redirect to cancel page
     */
    public function handleCancel(Request $request, string $gateway): RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'payment-cancel:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for payment cancellation', [
                    'gateway' => $gateway,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->route('user.dashboard')->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Clear payment session
            session()->forget('payment_productId');
            return redirect()->route('payment.cancel', $gateway)
                ->with('info', trans('app.Payment was cancelled. You can try again anytime.'));
        } catch (\Exception $e) {
            Log::error('Payment cancellation handling failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'gateway' => $gateway,
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->route('user.dashboard')->with('error', 'Payment cancellation failed. Please try again.');
        }
    }
    /**
     * Handle payment failure with enhanced security.
     *
     * Processes failed payment requests with proper session cleanup
     * and error logging.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $gateway  The payment gateway name
     *
     * @return RedirectResponse Redirect to failure page
     *
     * @example
     * // Payment fails:
     * GET /payment/failure/{gateway}
     * // Returns: Redirect to failure page
     */
    public function handleFailure(Request $request, string $gateway): RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'payment-failure:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                Log::warning('Rate limit exceeded for payment failure handling', [
                    'gateway' => $gateway,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->route('user.dashboard')->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            // Clear payment session
            session()->forget('payment_productId');
            $error = $this->sanitizeInput($request->get('error', 'Unknown error'));
            Log::warning('Payment failure', [
                'gateway' => $gateway,
                'error' => $error,
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->route('payment.failure', $gateway)
                ->with('error_message', trans(
                    'app.Payment failed: :error',
                    ['error' => is_string($error) ? $error : 'Unknown error']
                ));
        } catch (\Exception $e) {
            Log::error('Payment failure handling failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'gateway' => $gateway,
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->route('user.dashboard')->with(
                'error',
                'Payment failure handling failed. Please try again.',
            );
        }
    }
    /**
     * Handle webhook notifications from payment gateways with enhanced security.
     *
     * Processes webhook notifications from payment gateways with
     * comprehensive validation and security measures.
     *
     * @param  Request  $request  The HTTP request from webhook
     * @param  string  $gateway  The payment gateway name
     *
     * @return JsonResponse JSON response for webhook
     *
     * @example
     * // Webhook from PayPal/Stripe:
     * POST /payment/webhook/{gateway}
     * // Returns: JSON response
     */
    public function handleWebhook(Request $request, string $gateway): JsonResponse
    {
        try {
            // Rate limiting for webhooks
            $key = 'payment-webhook:' . request()->ip();
            if (RateLimiter::tooManyAttempts($key, 100)) {
                Log::warning('Rate limit exceeded for payment webhook', [
                    'gateway' => $gateway,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return response()->json(['status' => 'error', 'message' => 'Rate limit exceeded'], 429);
            }
            RateLimiter::hit($key, 60); // 1 minute
            $serviceRequest = new \App\Services\Request($request->all());
            $result = $this->paymentService->handleWebhook($serviceRequest, $gateway);
            if ($result['success']) {
                return response()->json(['status' => 'success']);
            } else {
                Log::warning('Webhook processing failed', [
                    'gateway' => $gateway,
                    'message' => $result['message'],
                    'ip' => request()->ip(),
                ]);
                return response()->json(['status' => 'error', 'message' => $result['message']], 400);
            }
        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'gateway' => $gateway,
                'ip' => request()->ip(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }
    }
    /**
     * Process custom invoice payment with enhanced security.
     *
     * Processes payments for custom invoices (additional services)
     * with comprehensive validation and security measures.
     *
     * @param  Request  $request  The HTTP request containing payment data
     * @param  Invoice  $invoice  The custom invoice to pay
     *
     * @return RedirectResponse Redirect to payment gateway or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /payment/custom/{invoice}
     * {
     *     "gateway": "stripe"
     * }
     */
    public function processCustomPayment(Request $request, Invoice $invoice): \Illuminate\Http\RedirectResponse
    {
        try {
            // Rate limiting
            $key = 'payment-custom:' . (Auth::id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($key, 5)) {
                Log::warning('Rate limit exceeded for custom payment processing', [
                    'invoice_id' => $invoice->id,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return redirect()->back()->with('error', 'Too many requests. Please try again later.');
            }
            RateLimiter::hit($key, 300); // 5 minutes
            $validated = $request->validate([
                'gateway' => 'required|in:stripe, paypal',
            ]);
            $validatedArray = is_array($validated) ? $validated : [];
            $gateway = is_string($validatedArray['gateway'] ?? null) ? $validatedArray['gateway'] : '';
            // Check if gateway is enabled
            if (! PaymentSetting::isGatewayEnabled($gateway)) {
                Log::warning('Disabled gateway access attempt for custom payment', [
                    'gateway' => $gateway,
                    'invoice_id' => $invoice->id,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                return redirect()->back()->with('error', 'Payment gateway is not available');
            }
            // Check if user owns this invoice
            if ($invoice->userId !== Auth::id()) {
                Log::warning('Unauthorized custom invoice access attempt', [
                    'invoice_id' => $invoice->id,
                    'invoice_userId' => $invoice->userId,
                    'current_userId' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                abort(403);
            }
            // Check if invoice is pending
            if ($invoice->status !== 'pending') {
                Log::warning('Invalid custom invoice status access attempt', [
                    'invoice_id' => $invoice->id,
                    'status' => $invoice->status,
                    'userId' => Auth::id(),
                    'ip' => request()->ip(),
                ]);
                return redirect()->back()->with('error', 'Invoice is not available for payment');
            }
            // Create order data for custom invoice
            $orderData = [
                'userId' => $invoice->userId,
                'productId' => null, // No product for custom invoices
                'amount' => $invoice->amount,
                'currency' => $invoice->currency,
                'invoice_id' => $invoice->id,
                'is_custom' => true,
            ];
            // Store payment data in session
            session(['payment_invoice_id' => $invoice->id]);
            session(['payment_is_custom' => true]);
            // Process payment
            $result = $this->paymentService->processPayment($orderData, $gateway);
            Log::warning('Custom payment result', [
                'result' => $this->sanitizeLogData($result),
                'invoice_id' => $invoice->id,
                'userId' => Auth::id(),
            ]);
            if ($result['success']) {
                return redirect()->to(is_string($result['redirect_url'] ?? null) ? $result['redirect_url'] : '/');
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Custom payment validation failed', [
                'errors' => $e->errors(),
                'invoice_id' => $invoice->id,
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Custom payment processing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
                'userId' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            return redirect()->back()->with('error', 'Payment processing failed');
        }
    }
    /**
     * Sanitize data for logging (remove sensitive information).
     *
     * @param  array<string, mixed>  $data  The data to sanitize
     *
     * @return array<string, mixed> The sanitized data
     */
    private function sanitizeLogData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'api_token',
            'licenseKey',
            'purchase_code',
            'credit_card',
            'ssn',
            'token',
            'secret',
            'key',
            'auth_token',
            'access_token',
            'refresh_token',
            'paymentId',
            'payment_intent',
            'session_id',
        ];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        return $data;
    }
}
