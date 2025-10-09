<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Helpers\SecureFileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgrammingLanguageAdvancedRequest;
use App\Http\Requests\Admin\ProgrammingLanguageRequest;
use App\Models\ProgrammingLanguage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

/**
 * Admin Programming Language Controller with enhanced security.
 *
 * This controller handles comprehensive programming language management in the admin panel
 * including creation, editing, template management, and validation functionality.
 *
 * Features:
 * - Programming language CRUD operations with validation
 * - Template file management and validation
 * - License template content management
 * - CSV export functionality
 * - Template syntax validation
 * - File upload and content creation
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (XSS protection, input validation)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 */
class ProgrammingLanguageController extends Controller
{
    /**
     * Display a listing of programming languages with pagination.
     *
     * Shows all programming languages with their template information
     * and provides both view aliases for compatibility.
     *
     * @return View The programming languages index view
     *
     * @version 1.0.6
     */
    public function index(): View
    {
        $languages = ProgrammingLanguage::orderBy('sort_order')->orderBy('name')->paginate(15);
        $availableTemplates = ProgrammingLanguage::getAvailableTemplateFiles();

        // Some views expect $programmingLanguages, others expect $languages — provide both aliases
        return view('admin.programming-languages.index', [
            'programmingLanguages' => $languages,
            'languages' => $languages,
            'availableTemplates' => $availableTemplates,
        ]);
    }

    /**
     * Get license file content for viewing with enhanced security.
     *
     * Retrieves license template content for a specific programming language
     * with proper file validation and error handling.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request containing type parameter
     * @param  string  $language  The programming language slug
     *
     * @return JsonResponse JSON response with file content or error
     *
     * @throws \Exception When file operations fail
     *
     * @version 1.0.6
     */
    public function getLicenseFileContent(\Illuminate\Http\Request $request, string $language): JsonResponse
    {
        try {
            $type = $request->get('type', 'default');
            $content = '';
            if ($type === 'default') {
                // Get default template file
                $templateDir = resource_path('templates/licenses');
                $files = glob($templateDir . '/' . $language . '.{php, blade.php}', GLOB_BRACE);
                if (! empty($files)) {
                    $file = $files[0];
                    if (file_exists($file)) {
                        $content = \Illuminate\Support\Facades\Storage::disk('local')->get($file);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Default template file not found',
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Default template file not found for language: ' . $language,
                    ], 404);
                }
            } else {
                // Get custom template from language
                $programmingLanguage = ProgrammingLanguage::where('slug', $language)->first();
                if (! $programmingLanguage) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Programming language not found',
                    ], 404);
                }
                $templatePath = $programmingLanguage->getTemplateFilePath();
                if (file_exists($templatePath)) {
                    $content = file_get_contents($templatePath);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Custom template file not found',
                    ], 404);
                }
            }

            return response()->json([
                'success' => true,
                'content' => $content,
                'language' => $language,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            // License file content error
            return response()->json([
                'success' => false,
                'message' => 'Error reading file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new programming language.
     *
     * Displays the programming language creation form with
     * all necessary fields for language configuration.
     *
     * @return View The programming language creation form view
     *
     * @version 1.0.6
     */
    public function create(): View
    {
        return view('admin.programming-languages.create');
    }

    /**
     * Store a newly created programming language with enhanced security.
     *
     * Creates a new programming language with comprehensive validation,
     * automatic slug generation, and proper error handling.
     *
     * @param  ProgrammingLanguageRequest  $request  The validated request containing language data
     *
     * @return RedirectResponse Redirect to languages list with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     */
    public function store(ProgrammingLanguageRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $validated['slug'] = $validated['slug'] ?? Str::slug(
                is_string($validated['name'] ?? null) ? $validated['name'] : ''
            );
            ProgrammingLanguage::create($validated);
            DB::commit();

            return redirect()->route('admin.programming-languages.index')
                ->with('success', 'Programming language created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programming language creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['license_template']),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create programming language. Please try again.');
        }
    }

    /**
     * Display the specified programming language with template information.
     *
     * Shows detailed information about a specific programming language
     * including available templates and configuration details.
     *
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to display
     *
     * @return View The programming language details view
     *
     * @version 1.0.6
     */
    public function show(ProgrammingLanguage $programmingLanguage): View
    {
        $availableTemplates = ProgrammingLanguage::getAvailableTemplateFiles();

        return view('admin.programming-languages.show', [
            'programming_language' => $programmingLanguage,
            'availableTemplates' => $availableTemplates
        ]);
    }

    /**
     * Show the form for editing the specified programming language.
     *
     * Displays the programming language edit form with populated data
     * for updating language configuration.
     *
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to edit
     *
     * @return View The programming language edit form view
     *
     * @version 1.0.6
     */
    public function edit(ProgrammingLanguage $programmingLanguage): View
    {
        return view('admin.programming-languages.edit', ['programming_language' => $programmingLanguage]);
    }

    /**
     * Update the specified programming language with enhanced security.
     *
     * Updates an existing programming language with comprehensive validation,
     * automatic slug generation, and proper error handling.
     *
     * @param  ProgrammingLanguageRequest  $request  The validated request containing updated language data
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to update
     *
     * @return RedirectResponse Redirect to languages list with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     */
    public function update(
        ProgrammingLanguageRequest $request,
        ProgrammingLanguage $programmingLanguage,
    ): RedirectResponse {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $validated['slug'] = $validated['slug'] ?? Str::slug(
                is_string($validated['name'] ?? null) ? $validated['name'] : ''
            );
            $programmingLanguage->update($validated);
            DB::commit();

            return redirect()->route('admin.programming-languages.index')
                ->with('success', 'Programming language updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programming language update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'programming_language_id' => $programmingLanguage->id,
                'request_data' => $request->except(['license_template']),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update programming language. Please try again.');
        }
    }

    /**
     * Remove the specified programming language with enhanced security.
     *
     * Deletes a programming language with proper validation to ensure
     * it's not being used by any products.
     *
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to delete
     *
     * @return RedirectResponse Redirect to languages list with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     */
    public function destroy(ProgrammingLanguage $programmingLanguage): RedirectResponse
    {
        try {
            DB::beginTransaction();
            // Check if language is being used by products
            if ($programmingLanguage->products()->count() > 0) {
                DB::rollBack();

                return redirect()->route('admin.programming-languages.index')
                    ->with('error', 'Cannot delete programming language that is being used by products.');
            }
            $programmingLanguage->delete();
            DB::commit();

            return redirect()->route('admin.programming-languages.index')
                ->with('success', 'Programming language deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programming language deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'programming_language_id' => $programmingLanguage->id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete programming language. Please try again.');
        }
    }

    /**
     * Toggle active status of the programming language with enhanced security.
     *
     * Toggles the programming language active status with proper
     * error handling and database transactions.
     *
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to toggle
     *
     * @return RedirectResponse Redirect back with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     */
    public function toggle(ProgrammingLanguage $programmingLanguage): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $programmingLanguage->update([
                'is_active' => ! $programmingLanguage->is_active,
            ]);
            $status = $programmingLanguage->is_active ? 'activated' : 'deactivated';
            DB::commit();

            return redirect()->route('admin.programming-languages.index')
                ->with('success', "Programming language {$status} successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programming language status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'programming_language_id' => $programmingLanguage->id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to update programming language status. Please try again.');
        }
    }

    /**
     * Get license template information for the programming language.
     *
     * Retrieves detailed template information including file existence,
     * size, and modification date for a specific programming language.
     *
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to get template info for
     *
     * @return JsonResponse JSON response with template information
     *
     * @version 1.0.6
     */
    public function getTemplateInfo(ProgrammingLanguage $programmingLanguage): JsonResponse
    {
        $templatePath = resource_path("templates/licenses/{$programmingLanguage->slug}.blade.php");
        $templateExists = file_exists($templatePath);
        $templateInfo = [
            'exists' => $templateExists,
            'path' => $templatePath,
            'size' => $templateExists ? filesize($templatePath) : 0,
            'last_modified' => $templateExists ? date('Y-m-d H:i:s', (int)filemtime($templatePath)) : null,
        ];

        return response()->json($templateInfo);
    }

    /**
     * Get all available license templates with file information.
     *
     * Retrieves a list of all available license template files
     * with their metadata including size and modification date.
     *
     * @return JsonResponse JSON response with available templates
     *
     * @version 1.0.6
     */
    public function getAvailableTemplates(): JsonResponse
    {
        $templateDir = resource_path('templates/licenses');
        $templates = [];
        if (is_dir($templateDir)) {
            $files = glob($templateDir . '/*.blade.php');
            if ($files !== false) {
                foreach ($files as $file) {
                    $filename = basename($file, '.blade.php');
                    $templates[] = [
                        'name' => $filename,
                        'path' => $file,
                        'size' => filesize($file),
                        'last_modified' => date('Y-m-d H:i:s', (int)filemtime($file)),
                    ];
                }
            }
        }

        return response()->json($templates);
    }

    /**
     * Validate template files with comprehensive syntax checking.
     *
     * Validates all available template files for syntax errors,
     * required placeholders, and file permissions.
     *
     * @return JsonResponse JSON response with validation results
     *
     * @version 1.0.6
     */
    public function validateTemplates(): JsonResponse
    {
        try {
            $availableTemplates = ProgrammingLanguage::getAvailableTemplateFiles();
            $validationResults = [];
            foreach ($availableTemplates as $templateName => $templateInfo) {
                $templatePath = (is_array($templateInfo) && isset($templateInfo['file_path']))
                    ? $templateInfo['file_path']
                    : '';
                $content = file_get_contents(is_string($templatePath) ? $templatePath : '');
                $result = [
                    'template' => $templateName,
                    'file_path' => $templatePath,
                    'is_valid' => true,
                    'errors' => [],
                    'warnings' => [],
                ];
                // Check for required placeholders
                $requiredPlaceholders = [
                    '{PRODUCT_NAME}',
                    '{DOMAIN}',
                    '{LICENSE_CODE}',
                    '{VALID_UNTIL}',
                    '{PRODUCT_VERSION}',
                ];
                foreach ($requiredPlaceholders as $placeholder) {
                    if ($content !== false && strpos($content, $placeholder) === false) {
                        $result['warnings'][] = "Missing placeholder: {$placeholder}";
                    }
                }
                // Basic syntax validation for PHP templates
                if (pathinfo(is_string($templatePath) ? $templatePath : '', PATHINFO_EXTENSION) === 'php') {
                    $syntaxCheck = $content !== false ? $this->validatePHPSyntax($content) : false;
                    if (! $syntaxCheck) {
                        $result['is_valid'] = false;
                        $result['errors'][] = 'PHP syntax error detected';
                    }
                }
                // Check file permissions
                if (! is_readable(is_string($templatePath) ? $templatePath : '')) {
                    $result['warnings'][] = 'File is not readable';
                }
                if (! is_writable(is_string($templatePath) ? $templatePath : '')) {
                    $result['warnings'][] = 'File is not writable';
                }
                $validationResults[] = $result;
            }

            return response()->json([
                'success' => true,
                'validation_results' => $validationResults,
                'summary' => [
                    'total_templates' => count($validationResults),
                    'valid_templates' => count(
                        array_filter($validationResults, fn ($r) => $r['is_valid']),
                    ),
                    'templates_with_errors' => count(
                        array_filter($validationResults, fn ($r) => ! empty($r['errors'])),
                    ),
                    'templates_with_warnings' => count(
                        array_filter($validationResults, fn ($r) => ! empty($r['warnings'])),
                    ),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate PHP syntax.
     *
     * @return array<string, mixed>
     */
    private function validatePHPSyntax(string $code): array
    {
        // Create a temporary file for syntax checking
        $tempFile = tempnam(sys_get_temp_dir(), 'php_syntax_check');
        SecureFileHelper::putContents($tempFile, $code);
        // Run PHP syntax check using Symfony Process (safer than shell_exec)
        $process = new Process(['php', '-l', $tempFile]);
        $process->run();
        $output = $process->getOutput() . $process->getErrorOutput();
        // Clean up
        SecureFileHelper::deleteFile($tempFile);
        if (strpos($output, 'No syntax errors detected') === false) {
            return [
                'valid' => false,
                'error' => trim($output),
            ];
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Upload template file with enhanced security.
     *
     * Uploads a template file for a specific programming language
     * with proper file validation and security measures.
     *
     * @param  ProgrammingLanguageAdvancedRequest  $request  The validated request containing template file
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to upload template for
     *
     * @return JsonResponse JSON response with upload result
     *
     * @throws \Exception When file operations fail
     *
     * @version 1.0.6
     */
    public function uploadTemplate(
        ProgrammingLanguageAdvancedRequest $request,
        ProgrammingLanguage $programmingLanguage,
    ): JsonResponse {
        try {
            $file = $request->file('template_file');
            $templateDir = resource_path('templates/licenses');
            // Create directory if it doesn't exist
            if (! is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }
            $filename = $programmingLanguage->slug . '.php';
            $file->move($templateDir, $filename);

            return response()->json([
                'success' => true,
                'message' => 'Template file uploaded successfully',
                'file_path' => $templateDir . '/' . $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload template file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create template file from textarea content with enhanced security.
     *
     * Creates a template file from provided content for a specific
     * programming language with proper validation and security measures.
     *
     * @param  ProgrammingLanguageAdvancedRequest  $request  The validated request containing template content
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to create template for
     *
     * @return JsonResponse JSON response with creation result
     *
     * @throws \Exception When file operations fail
     *
     * @version 1.0.6
     */
    public function createTemplateFile(
        ProgrammingLanguageAdvancedRequest $request,
        ProgrammingLanguage $programmingLanguage,
    ): JsonResponse {
        try {
            $templateDir = resource_path('templates/licenses');
            // Create directory if it doesn't exist
            if (! is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }
            $filename = $programmingLanguage->slug . '.php';
            $filePath = $templateDir . '/' . $filename;
            SecureFileHelper::putContents(
                $filePath,
                is_string($request->template_content ?? null)
                    ? $request->template_content
                    : ''
            );

            return response()->json([
                'success' => true,
                'message' => 'Template file created successfully',
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export programming languages to CSV format with comprehensive data.
     *
     * Generates a CSV file containing all programming languages with their
     * configuration details for administrative purposes.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse The CSV download response
     *
     * @version 1.0.6
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $languages = ProgrammingLanguage::orderBy('sort_order')->orderBy('name')->get();
        $filename = 'programming_languages_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($languages) {
            $file = fopen('php://output', 'w');
            if ($file !== false) {
                // CSV Headers
                fputcsv($file, [
                'ID',
                'Name',
                'Slug',
                'Description',
                'Icon',
                'File Extension',
                'Is Active',
                'Sort Order',
                'Created At',
                'Updated At',
                ]);
            // CSV Data
                foreach ($languages as $language) {
                    fputcsv($file, [
                    $language->id,
                    $language->name,
                    $language->slug,
                    $language->description,
                    $language->icon,
                    $language->file_extension,
                    $language->is_active ? 'Yes' : 'No',
                    $language->sort_order,
                    $language->created_at?->format('Y-m-d H:i:s'),
                    $language->updated_at?->format('Y-m-d H:i:s'),
                    ]);
                }
                fclose($file);
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get template content for a programming language with file information.
     *
     * Retrieves the template content for a specific programming language
     * along with file metadata including size and modification date.
     *
     * @param  ProgrammingLanguage  $programmingLanguage  The programming language to get template content for
     *
     * @return JsonResponse JSON response with template content or error
     *
     * @version 1.0.6
     */
    public function getTemplateContent(ProgrammingLanguage $programmingLanguage): JsonResponse
    {
        $templatePath = resource_path("templates/licenses/{$programmingLanguage->slug}.blade.php");
        if (file_exists($templatePath)) {
            $content = file_get_contents($templatePath);

            return response()->json([
                'success' => true,
                'content' => $content,
                'file_path' => $templatePath,
                'file_size' => SecureFileHelper::getFileSize($templatePath),
                'last_modified' => date('Y-m-d H:i:s', (int)filemtime($templatePath)),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Template file not found',
            'file_path' => $templatePath,
        ], 404);
    }
}
