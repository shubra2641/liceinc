<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentSettingsRequest;
use App\Models\PaymentSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Payment Settings Controller with enhanced security.
 *
 * This controller handles payment gateway settings management including
 * PayPal and Stripe configuration, testing, and validation.
 *
 * Features:
 * - Payment gateway settings management
 * - PayPal and Stripe configuration
 * - Connection testing functionality
 * - Credential validation and security
 * - Webhook URL management
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 */
class PaymentSettingsController extends Controller
{
    /**
     * Display payment settings with enhanced security.
     *
     * Shows payment gateway settings for PayPal and Stripe with proper
     * error handling and security measures.
     *
     * @return View The payment settings view
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Access payment settings:
     * GET /admin/payment-settings
     *
     * // Returns view with:
     * // - PayPal configuration settings
     * // - Stripe configuration settings
     * // - Connection status indicators
     */
    public function index(): View
    {
        try {
            DB::beginTransaction();
            $paypalSettings = PaymentSetting::getByGateway('paypal');
            $stripeSettings = PaymentSetting::getByGateway('stripe');
            DB::commit();
            return view('admin.payment-settings.index', compact('paypalSettings', 'stripeSettings'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment settings loading failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return empty settings on error
            return view('admin.payment-settings.index', [
                'paypalSettings' => null,
                'stripeSettings' => null,
            ]);
        }
    }
    /**
     * Update payment settings with enhanced security.
     *
     * Updates payment gateway settings with comprehensive validation,
     * credential security, and proper error handling.
     *
     * @param  PaymentSettingsUpdateRequest  $request  The HTTP request containing payment settings
     *
     * @return JsonResponse JSON response with update result
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Update PayPal settings:
     * POST /admin/payment-settings
     * {
     *     "gateway": "paypal",
     *     "is_enabled": true,
     *     "is_sandbox": true,
     *     "credentials": {
     *         "client_id": "your_client_id",
     *         "client_secret": "your_client_secret"
     *     },
     *     "webhook_url": "https://example.com/webhook"
     * }
     */
    public function update(PaymentSettingsRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $gateway = $validated['gateway'];
            $settings = PaymentSetting::getByGateway($gateway);
            if (! $settings) {
                DB::rollBack();
                return redirect()->back()->with('error', trans('app.Payment gateway not found'));
            }
            // Get validated credentials (already sanitized by Request class)
            $credentials = $validated['credentials'];
            $webhookUrl = $validated['webhook_url'] ?? null;
            $settings->update([
                'is_enabled' => $validated['is_enabled'] ?? false,
                'is_sandbox' => $validated['is_sandbox'] ?? false,
                'credentials' => $credentials,
                'webhook_url' => $webhookUrl,
            ]);
            DB::commit();
            return redirect()->back()->with('success', trans('app.Payment settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment settings update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'gateway' => $request->gateway ?? 'unknown',
            ]);
            return redirect()->back()->with('error', trans('app.Failed to update payment settings'));
        }
    }
    /**
     * Test payment gateway connection with enhanced security.
     *
     * Tests payment gateway connection with provided credentials
     * using secure validation and proper error handling.
     *
     * @param  PaymentSettingsTestRequest  $request  The HTTP request containing gateway credentials
     *
     * @return JsonResponse JSON response with test result
     *
     * @throws \Exception When connection test fails
     *
     * @example
     * // Test PayPal connection:
     * POST /admin/payment-settings/test-connection
     * {
     *     "gateway": "paypal",
     *     "credentials": {
     *         "client_id": "your_client_id",
     *         "client_secret": "your_client_secret"
     *     }
     * }
     */
    public function testConnection(PaymentSettingsRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $gateway = $validated['gateway'];
            $credentials = $validated['credentials'];
            if ($gateway === 'paypal') {
                $result = $this->testPayPalConnection($credentials);
            } elseif ($gateway === 'stripe') {
                $result = $this->testStripeConnection($credentials);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => trans('app.Unsupported payment gateway'),
                ], 400);
            }
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Payment gateway connection test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'gateway' => $request->gateway ?? 'unknown',
            ]);
            return response()->json([
                'success' => false,
                'message' => trans('app.Connection test failed: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
    /**
     * Test PayPal connection with enhanced security.
     *
     * Tests PayPal API connection using provided credentials
     * with proper error handling and security measures.
     *
     * @param  array  $credentials  The PayPal credentials to test
     *
     * @return array Test result with success status and message
     *
     * @throws \Exception When PayPal API operations fail
     */
    protected function testPayPalConnection(array $credentials): array
    {
        try {
            // Validate required credentials
            if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
                return [
                    'success' => false,
                    'message' => trans('app.PayPal credentials are incomplete'),
                ];
            }
            $paypal = new \PayPal\PayPalServerSDK\PayPalServerSDK([
                'clientId' => $credentials['client_id'],
                'clientSecret' => $credentials['client_secret'],
                'mode' => 'sandbox', // Always test in sandbox mode
            ]);
            // Try to create a simple order to test connection
            $request = new \PayPal\PayPalServerSDK\Orders\OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => '1.00',
                        ],
                    ],
                ],
            ];
            $response = $paypal->client()->execute($request);
            if ($response->statusCode === 201) {
                return [
                    'success' => true,
                    'message' => trans('app.PayPal connection successful'),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => trans('app.PayPal connection failed'),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('PayPal connection test failed', [
                'error' => $e->getMessage(),
                'client_id' => substr($credentials['client_id'] ?? '', 0, 8) . '...',
            ]);
            return [
                'success' => false,
                'message' => trans('app.PayPal connection error: :error', ['error' => $e->getMessage()]),
            ];
        }
    }
    /**
     * Test Stripe connection with enhanced security.
     *
     * Tests Stripe API connection using provided credentials
     * with proper error handling and security measures.
     *
     * @param  array  $credentials  The Stripe credentials to test
     *
     * @return array Test result with success status and message
     *
     * @throws \Exception When Stripe API operations fail
     */
    protected function testStripeConnection(array $credentials): array
    {
        try {
            // Validate required credentials
            if (empty($credentials['secret_key'])) {
                return [
                    'success' => false,
                    'message' => trans('app.Stripe secret key is required'),
                ];
            }
            \Stripe\Stripe::setApiKey($credentials['secret_key']);
            // Try to retrieve account information
            $account = \Stripe\Account::retrieve();
            if ($account && $account->id) {
                return [
                    'success' => true,
                    'message' => trans('app.Stripe connection successful'),
                    'account_id' => $account->id,
                    'account_name' => $account->business_profile->name ?? 'N/A',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => trans('app.Stripe connection failed'),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Stripe connection test failed', [
                'error' => $e->getMessage(),
                'secret_key' => substr($credentials['secret_key'] ?? '', 0, 8) . '...',
            ]);
            return [
                'success' => false,
                'message' => trans('app.Stripe connection error: :error', ['error' => $e->getMessage()]),
            ];
        }
    }
}
