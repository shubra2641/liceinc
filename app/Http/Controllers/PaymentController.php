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
use Illuminate\View\View;

/**
 * Payment Controller - Ultra Simplified.
 *
 * Handles payment processing with minimal complexity.
 */
class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private EmailFacade $emailService,
    ) {
    }

    /**
     * Show payment gateways.
     */
    public function showPaymentGateways(Product $product): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', trans('app.Please login to purchase this product'));
        }

        if (! $product->is_active || $product->price <= 0) {
            return redirect()->back()->with('error', trans('app.Product is not available for purchase'));
        }

        $enabledGateways = PaymentSetting::getEnabledGateways();
        if (empty($enabledGateways)) {
            return redirect()->back()->with('error', trans('app.No payment gateways are currently available'));
        }

        return view('payment.gateways', [
            'product' => $product,
            'enabledGateways' => $enabledGateways,
        ]);
    }

    /**
     * Process payment.
     */
    public function processPayment(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'gateway' => 'required|in:paypal,stripe',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        if (! Auth::check()) {
            return redirect()->back()->with('error', trans('app.Please login to purchase this product'));
        }

        if (! PaymentSetting::isGatewayEnabled($validated['gateway'])) {
            return redirect()->back()->with('error', trans('app.Selected payment gateway is not available'));
        }

        DB::beginTransaction();

        $invoice = null;
        if ($validated['invoice_id']) {
            $invoice = Invoice::where('id', $validated['invoice_id'])
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if (! $invoice) {
                DB::rollBack();

                return redirect()->back()->with('error', trans('app.Invoice not found or already paid'));
            }
        }

        $orderData = [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'amount' => $invoice ? $invoice->amount : $product->price,
            'currency' => 'USD',
            'payment_gateway' => $validated['gateway'],
            'payment_status' => 'pending',
            'invoice_id' => $invoice ? $invoice->id : null,
        ];

        session(['payment_product_id' => $product->id]);
        if ($invoice) {
            session(['payment_invoice_id' => $invoice->id]);
        }

        $paymentResult = $this->paymentService->processPayment($orderData, $validated['gateway']);

        if ($validated['gateway'] === 'stripe' && isset($paymentResult['session_id'])) {
            session(['stripe_session_id' => $paymentResult['session_id']]);
        }

        DB::commit();

        return redirect()->to($paymentResult['redirect_url'] ?? '/');
    }

    /**
     * Handle payment success.
     */
    public function handleSuccess(Request $request, string $gateway): RedirectResponse
    {
        $transactionId = $this->getTransactionId($request, $gateway);
        if (! $transactionId) {
            return redirect()->route('user.dashboard')->with('error', trans('app.Invalid payment response'));
        }

        $verificationResult = $this->paymentService->verifyPayment($gateway, $transactionId);
        if (! $verificationResult['success']) {
            return redirect()->route('payment.failure-page', $gateway)
                ->with('error_message', trans('app.Payment verification failed. Please contact support.'));
        }

        if (session('payment_is_custom', false)) {
            return $this->handleCustomPayment($transactionId, $gateway);
        }

        return $this->handleProductPayment($transactionId, $gateway);
    }

    /**
     * Handle payment cancel.
     */
    public function handleCancel(Request $request, string $gateway): RedirectResponse
    {
        session()->forget('payment_product_id');

        return redirect()->route('payment.cancel', $gateway)
            ->with('info', trans('app.Payment was cancelled. You can try again anytime.'));
    }

    /**
     * Handle payment failure.
     */
    public function handleFailure(Request $request, string $gateway): RedirectResponse
    {
        session()->forget('payment_product_id');
        $error = $request->get('error', 'Unknown error');

        return redirect()->route('payment.failure', $gateway)
            ->with('error_message', trans('app.Payment failed: :error', ['error' => $error]));
    }

    /**
     * Handle webhook.
     */
    public function handleWebhook(Request $request, string $gateway): JsonResponse
    {
        $serviceRequest = new \App\Services\Request($request->all());
        $result = $this->paymentService->handleWebhook($serviceRequest, $gateway);

        if ($result['success']) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'error', 'message' => $result['message']], 400);
        }
    }

    /**
     * Process custom payment.
     */
    public function processCustomPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'gateway' => 'required|in:stripe,paypal',
        ]);

        if (! PaymentSetting::isGatewayEnabled($validated['gateway'])) {
            return redirect()->back()->with('error', 'Payment gateway is not available');
        }

        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->status !== 'pending') {
            return redirect()->back()->with('error', 'Invoice is not available for payment');
        }

        $orderData = [
            'user_id' => $invoice->user_id,
            'product_id' => null,
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'invoice_id' => $invoice->id,
            'is_custom' => true,
        ];

        session(['payment_invoice_id' => $invoice->id]);
        session(['payment_is_custom' => true]);

        $result = $this->paymentService->processPayment($orderData, $validated['gateway']);

        if ($result['success']) {
            return redirect()->to($result['redirect_url'] ?? '/');
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Get transaction ID.
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
     * Handle custom payment.
     */
    private function handleCustomPayment(string $transactionId, string $gateway): RedirectResponse
    {
        $invoiceId = session('payment_invoice_id');
        $invoice = Invoice::find($invoiceId);

        if (! $invoice) {
            return redirect()->route('user.dashboard')->with('error', trans('app.Invoice not found'));
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'metadata' => array_merge($invoice->metadata ?? [], [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
            ]),
        ]);

        try {
            $this->emailService->sendCustomInvoicePaymentConfirmation($invoice);
            $this->emailService->sendAdminCustomInvoicePaymentNotification($invoice);
        } catch (\Exception $e) {
            Log::error('Failed to send custom invoice payment emails', ['error' => $e->getMessage()]);
        }

        session()->forget(['payment_invoice_id', 'payment_is_custom']);

        return redirect()->route('payment.success-page', $gateway)
            ->with('success', trans('app.Payment successful! Your service payment has been processed.'))
            ->with('invoice', $invoice);
    }

    /**
     * Handle product payment.
     */
    private function handleProductPayment(string $transactionId, string $gateway): RedirectResponse
    {
        $productId = session('payment_product_id');
        $product = $productId ? Product::find($productId) :
            Product::where('is_active', true)->where('price', '>', 0)->first();

        if (! $product) {
            return redirect()->route('user.dashboard')->with('error', trans('app.No products available for purchase'));
        }

        $invoiceId = session('payment_invoice_id');
        $existingInvoice = $invoiceId ? Invoice::find($invoiceId) : null;

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
            session()->forget(['payment_product_id', 'payment_invoice_id']);

            try {
                if ($result['license'] instanceof \App\Models\License && $result['invoice'] instanceof Invoice) {
                    $this->emailService->sendPaymentConfirmation($result['license'], $result['invoice']);
                    $this->emailService->sendLicenseCreated($result['license']);
                    $this->emailService->sendAdminPaymentNotification($result['license'], $result['invoice']);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send payment emails', ['error' => $e->getMessage()]);
            }

            return redirect()->route('payment.success-page', $gateway)
                ->with('license', $result['license'])
                ->with('invoice', $result['invoice'])
                ->with('success', trans('app.Payment successful! Your license has been activated.'));
        } else {
            return redirect()->route('payment.failure-page', $gateway)
                ->with('error_message', trans('app.Payment successful but failed to create license. ' .
                    'Please contact support.'));
        }
    }
}
