<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\Email\EmailFacade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Email Template Controller with enhanced security.
 *
 * This controller handles email template management in the admin panel,
 * including CRUD operations, template testing, and email sending functionality.
 * It provides comprehensive template management with security measures.
 *
 * Features:
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling with database transactions
 * - Proper logging for errors and warnings only
 * - Email template CRUD operations
 * - Template testing and preview functionality
 * - Email sending with validation
 * - Search and filtering capabilities
 * - Template activation/deactivation
 */
class EmailTemplateController extends Controller
{
    protected EmailFacade $emailService;

    /**
     * Create a new controller instance.
     *
     * @param  EmailFacade  $emailService  The email service for sending emails
     *
     * @version 1.0.6
     */
    public function __construct(EmailFacade $emailService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of email templates with enhanced security.
     *
     * Shows a paginated list of email templates with filtering by type,
     * category, active status, and search functionality. Includes proper
     * input sanitization and error handling.
     *
     * @param  Request  $request  The HTTP request containing filter parameters
     *
     * @return View The email templates index view
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     * @example
     * // Request with filters:
     * GET /admin/email-templates?type=user&category=license&search=welcome&is_active=1
     *
     * // Returns view with:
     * // - Paginated templates list
     * // - Filter options (types, categories)
     * // - Search functionality
     */
    public function index(Request $request): View
    {
        try {
            DB::beginTransaction();
            $query = EmailTemplate::query();
            // Filter by type with validation
            if ($request->filled('type')) {
                $type = $this->sanitizeInput($request->type);
                if (in_array($type, ['user', 'admin'])) {
                    $query->where('type', $type);
                }
            }
            // Filter by category with validation
            if ($request->filled('category')) {
                $category = $this->sanitizeInput($request->category);
                $query->where('category', $category);
            }
            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }
            // Search with sanitization
            if ($request->filled('search')) {
                $search = $this->sanitizeInput($request->search);
                $query->where(function ($q) use ($search) {
                    $searchStr = is_string($search) ? $search : '';
                    $q->where('name', 'like', "%{$searchStr}%")
                        ->orWhere('subject', 'like', "%{$searchStr}%")
                        ->orWhere('description', 'like', "%{$searchStr}%");
                });
            }
            $templates = $query->orderBy('type')
                ->orderBy('category')
                ->orderBy('name')
                ->paginate(20);
            $types = EmailTemplate::distinct()->pluck('type')->sort();
            $categories = EmailTemplate::distinct()->pluck('category')->sort();
            DB::commit();

            return view('admin.email-templates.index', [
                'templates' => $templates,
                'types' => $types,
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email templates listing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty results on error
            return view('admin.email-templates.index', [
                'templates' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'types' => collect(),
                'categories' => collect(),
            ]);
        }
    }

    /**
     * Show the form for creating a new email template.
     *
     * Displays the email template creation form with predefined
     * types and categories for template creation.
     *
     * @return View The email template creation form view
     *
     * @version 1.0.6
     *
     * @example
     * // Access the create form:
     * GET /admin/email-templates/create
     *
     * // Returns view with:
     * // - Template name and subject fields
     * // - Body editor with variables
     * // - Type selection (user/admin)
     * // - Category selection
     * // - Active status toggle
     */
    public function create(): View
    {
        $types = ['user', 'admin'];
        $categories = ['registration', 'license', 'product', 'ticket', 'invoice'];

        return view('admin.email-templates.create', ['types' => $types, 'categories' => $categories]);
    }

    /**
     * Store a newly created email template with enhanced security.
     *
     * Creates a new email template with comprehensive validation including
     * XSS protection, input sanitization, and proper error handling.
     *
     * @param  EmailTemplateRequest  $request  The validated request containing template data
     *
     * @return RedirectResponse Redirect to template view or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     * @example
     * // Request:
     * POST /admin/email-templates
     * {
     *     "name": "Welcome Email",
     *     "subject": "Welcome to {{site_name}}",
     *     "body": "Hello {{user_name}}, welcome to our platform!",
     *     "type": "user",
     *     "category": "registration",
     *     "is_active": true
     * }
     *
     * // Success response: Redirect to template details
     * // "Email template created successfully."
     */
    public function store(EmailTemplateRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $template = EmailTemplate::create($validated);
            DB::commit();

            return redirect()
                ->route('admin.email-templates.show', $template)
                ->with('success', 'Email template created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['body']),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create email template. Please try again.');
        }
    }

    /**
     * Display the specified email template.
     *
     * Shows detailed information about a specific email template including
     * its content, variables, and management options.
     *
     * @param  EmailTemplate  $email_template  The email template to display
     *
     * @return View The email template show view
     *
     * @version 1.0.6
     *
     * @example
     * // Access template details:
     * GET /admin/email-templates/123
     *
     * // Returns view with:
     * // - Template details and content
     * // - Variable information
     * // - Action buttons (edit, test, toggle, delete)
     * // - Usage statistics
     */
    public function show(EmailTemplate $email_template): View
    {
        return view('admin.email-templates.show', ['email_template' => $email_template]);
    }

    /**
     * Show the form for editing the specified email template.
     *
     * Displays the email template editing form with pre-populated data
     * and predefined types and categories for template modification.
     *
     * @param  EmailTemplate  $email_template  The email template to edit
     *
     * @return View The email template edit form view
     *
     * @version 1.0.6
     *
     * @example
     * // Access the edit form:
     * GET /admin/email-templates/123/edit
     *
     * // Returns view with:
     * // - Pre-populated template data
     * // - Editable name, subject, and body fields
     * // - Type and category selection
     * // - Active status toggle
     * // - Variable management
     */
    public function edit(EmailTemplate $email_template): View
    {
        $types = ['user', 'admin'];
        $categories = ['registration', 'license', 'product', 'ticket', 'invoice'];

        return view('admin.email-templates.edit', [
            'email_template' => $email_template,
            'types' => $types,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified email template with enhanced security.
     *
     * Updates an existing email template with comprehensive validation including
     * XSS protection, input sanitization, and proper error handling.
     *
     * @param  EmailTemplateRequest  $request  The validated request containing template data
     * @param  EmailTemplate  $email_template  The email template to update
     *
     * @return RedirectResponse Redirect to template view or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     * @example
     * // Update template:
     * PUT /admin/email-templates/123
     * {
     *     "name": "Updated Welcome Email",
     *     "subject": "Welcome to {{site_name}} - Updated",
     *     "body": "Hello {{user_name}}, welcome to our updated platform!",
     *     "type": "user",
     *     "category": "registration",
     *     "is_active": true
     * }
     *
     * // Success response: Redirect to template details
     * // "Email template updated successfully."
     */
    public function update(EmailTemplateRequest $request, EmailTemplate $email_template): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $email_template->update($validated);
            DB::commit();

            return redirect()
                ->route('admin.email-templates.show', $email_template)
                ->with('success', 'Email template updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $email_template->id,
                'request_data' => $request->except(['body']),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update email template. Please try again.');
        }
    }

    /**
     * Remove the specified email template with enhanced security.
     *
     * Deletes an email template with proper error handling and database
     * transaction management to ensure data integrity.
     *
     * @param  EmailTemplate  $email_template  The email template to delete
     *
     * @return RedirectResponse Redirect to templates index or back with error
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     * @example
     * // Delete template:
     * DELETE /admin/email-templates/123
     *
     * // Success response: Redirect to templates list
     * // "Email template deleted successfully."
     *
     * // Error response: Redirect back with error
     * // "Failed to delete email template. Please try again."
     */
    public function destroy(EmailTemplate $email_template): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $email_template->delete();
            DB::commit();

            return redirect()
                ->route('admin.email-templates.index')
                ->with('success', 'Email template deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $email_template->id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete email template. Please try again.');
        }
    }

    /**
     * Toggle template active status with enhanced security.
     *
     * Toggles the active status of an email template with proper error
     * handling and database transaction management.
     *
     * @param  EmailTemplate  $email_template  The email template to toggle
     *
     * @return RedirectResponse Redirect back with success or error message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     * @example
     * // Toggle template status:
     * POST /admin/email-templates/123/toggle
     *
     * // Success response: Redirect back with success message
     * // "Email template activated successfully." or "Email template deactivated successfully."
     *
     * // Error response: Redirect back with error
     * // "Failed to toggle email template status. Please try again."
     */
    public function toggle(EmailTemplate $email_template): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $email_template->update(['is_active' => ! $email_template->is_active]);
            $status = $email_template->is_active ? 'activated' : 'deactivated';
            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Email template {$status} successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $email_template->id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to toggle email template status. Please try again.');
        }
    }

    /**
     * Test email template rendering with enhanced security.
     *
     * Renders an email template with test data to preview how it will
     * appear when sent. Includes comprehensive test data and error handling.
     *
     * @param  Request  $request  The HTTP request containing test data
     * @param  EmailTemplate  $email_template  The email template to test
     *
     * @return View The email template test view
     *
     * @throws \Exception When template rendering fails
     *
     * @version 1.0.6
     *
     * @example
     * // Test template rendering:
     * GET /admin/email-templates/123/test?test_data[user_name]=John&test_data[product_name]=Test Product
     *
     * // Returns view with:
     * // - Rendered template preview
     * // - Test data used for rendering
     * // - Variable substitution results
     * // - Send test email option
     */
    public function test(Request $request, EmailTemplate $email_template): View
    {
        try {
            $testData = $request->get('test_data', []);
            $testData = $this->prepareTestData(['test_data' => $testData, 'test_email' => 'test@example.com']);
            try {
                $rendered = $email_template->render($testData);
            } catch (\Exception $e) {
                Log::error('Email template rendering failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'template_id' => $email_template->id,
                ]);
                $rendered = [
                    'subject' => 'Error rendering template',
                    'body' => 'Error: ' . $e->getMessage(),
                ];
            }

            return view('admin.email-templates.test', [
                'email_template' => $email_template,
                'testData' => $testData,
                'rendered' => $rendered,
            ]);
        } catch (\Exception $e) {
            Log::error('Email template test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $email_template->id,
            ]);

            // Return error view
            return view('admin.email-templates.test', [
                'email_template' => $email_template,
                'testData' => [],
                'rendered' => [
                    'subject' => 'Error',
                    'body' => 'Failed to load test data.',
                ],
            ]);
        }
    }

    /**
     * Send test email with enhanced security.
     *
     * Sends a test email using the specified template with comprehensive
     * validation, input sanitization, and error handling.
     *
     * @param  EmailTemplateRequest  $request  The validated request containing test email data
     * @param  EmailTemplate  $email_template  The email template to test
     *
     * @return RedirectResponse Redirect back with success or error message
     *
     * @throws \Exception When email sending fails
     *
     * @version 1.0.6
     *
     * @example
     * // Send test email:
     * POST /admin/email-templates/123/send-test
     * {
     *     "test_email": "test@example.com",
     *     "test_data": {
     *         "user_name": "John Doe",
     *         "product_name": "Test Product"
     *     }
     * }
     *
     * // Success response: Redirect back with success message
     * // "Test email sent successfully."
     *
     * // Error response: Redirect back with error
     * // "Failed to send test email. Please check the logs for more details."
     */
    public function sendTest(EmailTemplateRequest $request, EmailTemplate $email_template): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $testData = $this->prepareTestData($validated);
            $emailService = app(EmailFacade::class);
            $success = $emailService->sendEmail(
                $email_template->name,
                is_string($validated['test_email']) ? $validated['test_email'] : '',
                $testData,
                'Test User',
            );
            if ($success) {
                DB::commit();

                return redirect()
                    ->back()
                    ->with('success', 'Test email sent successfully.');
            } else {
                Log::error('Email test failed - service returned false', [
                    'template' => $email_template->name,
                    'recipient' => $validated['test_email'],
                ]);
                DB::commit();

                return redirect()
                    ->back()
                    ->with('error', 'Failed to send test email. Please check the logs for more details.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email test exception', [
                'template' => $email_template->name,
                'recipient' => $request->get('test_email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error sending test email: ' . $e->getMessage());
        }
    }

    /**
     * Prepare test data for email template testing.
     *
     * Combines default test data with user-provided test data
     * for comprehensive template testing.
     *
     * @param  array  $validated  The validated request data
     *
     * @return array The prepared test data
     *
     * @version 1.0.6
     */
    /**
     * @param array<string, mixed> $validated
     *
     * @return array<string, mixed>
     */
    private function prepareTestData(array $validated): array
    {
        $testData = $validated['test_data'] ?? [];
        // Add default test data
        $defaultData = [
            'user_name' => 'Test User',
            'user_email' => $validated['test_email'],
            'site_name' => config('app.name'),
            'site_url' => config('app.url'),
            'current_year' => date('Y'),
            'verification_url' => (is_string(config('app.url'))
                ? config('app.url')
                : '') . '/verify-email?token=test-token',
            'reset_url' => (is_string(config('app.url')) ? config('app.url') : '') . '/reset-password?token=test-token',
            'license_key' => 'LIC-' . strtoupper(substr(md5((string)time()), 0, 8)),
            'product_name' => 'Test Product',
            'expires_at' => now()->addYear()->format('M d, Y'),
            'days_remaining' => 30,
            'ticket_id' => '12345',
            'ticket_subject' => 'Test Support Ticket',
            'invoice_number' => 'INV-001',
            'invoice_amount' => '99.99',
            'due_date' => now()->addDays(7)->format('M d, Y'),
            'payment_date' => now()->format('M d, Y'),
            'payment_method' => 'Credit Card',
        ];

        /**
 * @var array<string, mixed> $result
*/
        $result = array_merge($defaultData, is_array($testData) ? $testData : []);
        return $result;
    }
}
