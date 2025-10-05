<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgrammingLanguageAdvancedRequest;
use App\Http\Requests\Admin\ProgrammingLanguageRequest;
use App\Models\ProgrammingLanguage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Illuminate\View\View;
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
     *
     *
     *
     *
     */
    public function index(): View
    {
        $languages = ProgrammingLanguage::orderBy('sort_order')->orderBy('name')->paginate(15);
        $availableTemplates = ProgrammingLanguage::getAvailableTemplateFiles();
        // Some views expect $programming_languages, others expect $languages — provide both aliases
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
     * @param  Request  $request  The HTTP request containing type parameter
     * @param  string  $language  The programming language slug
     *
     * @return JsonResponse JSON response with file content or error
     *
     * @throws \Exception When file operations fail
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function getLicenseFileContent(Request $request, string $language): JsonResponse
    {
        try {
            $type = $request->get('type', 'default');
            $content = '';
            if ($type === 'default') {
                // Get default template file
                $templateDir = resource_path('templates/licenses');
                $files = glob($templateDir.'/'.$language.'.{php, blade.php}', GLOB_BRACE);
                if (! empty($files)) {
                    $file = $files[0];
                    if (file_exists($file)) {
                        $content = file_get_contents($file);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Default template file not found',
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Default template file not found for language: '.$language,
                    ], 404);
                }
            } else {
                // Get custom template from language
                $programming_language = ProgrammingLanguage::where('slug', $language)->first();
                if (! $programming_language) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Programming language not found',
                    ], 404);
                }
                $templatePath = $programming_language->getTemplateFilePath();
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
                'message' => 'Error reading file: '.$e->getMessage(),
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
     *
     *
     *
     *
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
     * @param  ProgrammingLanguageStoreRequest  $request  The validated request containing language data
     *
     * @return RedirectResponse Redirect to languages list with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function store(ProgrammingLanguageRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
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
     * @param  ProgrammingLanguage  $programming_language  The programming language to display
     *
     * @return View The programming language details view
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function show(ProgrammingLanguage $programming_language): View
    {
        $availableTemplates = ProgrammingLanguage::getAvailableTemplateFiles();
        return view('admin.programming-languages.show', compact('programming_language', 'availableTemplates'));
    }
    /**
     * Show the form for editing the specified programming language.
     *
     * Displays the programming language edit form with populated data
     * for updating language configuration.
     *
     * @param  ProgrammingLanguage  $programming_language  The programming language to edit
     *
     * @return View The programming language edit form view
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function edit(ProgrammingLanguage $programming_language): View
    {
        return view('admin.programming-languages.edit', compact('programming_language'));
    }
    /**
     * Update the specified programming language with enhanced security.
     *
     * Updates an existing programming language with comprehensive validation,
     * automatic slug generation, and proper error handling.
     *
     * @param  ProgrammingLanguageUpdateRequest  $request  The validated request containing updated language data
     * @param  ProgrammingLanguage  $programming_language  The programming language to update
     *
     * @return RedirectResponse Redirect to languages list with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function update(
        ProgrammingLanguageRequest $request,
        ProgrammingLanguage $programming_language,
    ): RedirectResponse {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
            $programming_language->update($validated);
            DB::commit();
            return redirect()->route('admin.programming-languages.index')
                ->with('success', 'Programming language updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programming language update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'programming_language_id' => $programming_language->id,
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
     * @param  ProgrammingLanguage  $programming_language  The programming language to delete
     *
     * @return RedirectResponse Redirect to languages list with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function destroy(ProgrammingLanguage $programming_language): RedirectResponse
    {
        try {
            DB::beginTransaction();
            // Check if language is being used by products
            if ($programming_language->products()->count() > 0) {
                DB::rollBack();
                return redirect()->route('admin.programming-languages.index')
                    ->with('error', 'Cannot delete programming language that is being used by products.');
            }
            $programming_language->delete();
            DB::commit();
            return redirect()->route('admin.programming-languages.index')
                ->with('success', 'Programming language deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programming language deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'programming_language_id' => $programming_language->id,
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
     * @param  ProgrammingLanguage  $programming_language  The programming language to toggle
     *
     * @return RedirectResponse Redirect back with success message
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function toggle(ProgrammingLanguage $programming_language): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $programming_language->update([
                'is_active' => ! $programming_language->is_active,
            ]);
            $status = $programming_language->is_active ? 'activated' : 'deactivated';
            DB::commit();
            return redirect()->route('admin.programming-languages.index')
                ->with('success', "Programming language {$status} successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Programming language status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'programming_language_id' => $programming_language->id,
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
     * @param  ProgrammingLanguage  $programming_language  The programming language to get template info for
     *
     * @return JsonResponse JSON response with template information
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function getTemplateInfo(ProgrammingLanguage $programming_language): JsonResponse
    {
        $templatePath = resource_path("templates/licenses/{$programming_language->slug}.blade.php");
        $templateExists = file_exists($templatePath);
        $templateInfo = [
            'exists' => $templateExists,
            'path' => $templatePath,
            'size' => $templateExists ? filesize($templatePath) : 0,
            'last_modified' => $templateExists ? date('Y-m-d H:i:s', filemtime($templatePath)) : null,
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
     *
     *
     *
     *
     */
    public function getAvailableTemplates(): JsonResponse
    {
        $templateDir = resource_path('templates/licenses');
        $templates = [];
        if (is_dir($templateDir)) {
            $files = glob($templateDir.'/*.blade.php');
            foreach ($files as $file) {
                $filename = basename($file, '.blade.php');
                $templates[] = [
                    'name' => $filename,
                    'path' => $file,
                    'size' => filesize($file),
                    'last_modified' => date('Y-m-d H:i:s', filemtime($file)),
                ];
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
     *
     *
     *
     *
     */
    public function validateTemplates(): JsonResponse
    {
        try {
            $availableTemplates = ProgrammingLanguage::getAvailableTemplateFiles();
            $validationResults = [];
            foreach ($availableTemplates as $templateName => $templateInfo) {
                $templatePath = $templateInfo['file_path'];
                $content = file_get_contents($templatePath);
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
                    if (strpos($content, $placeholder) === false) {
                        $result['warnings'][] = "Missing placeholder: {$placeholder}";
                    }
                }
                // Basic syntax validation for PHP templates
                if (pathinfo($templatePath, PATHINFO_EXTENSION) === 'php') {
                    $syntaxCheck = $this->validatePHPSyntax($content);
                    if (! $syntaxCheck['valid']) {
                        $result['is_valid'] = false;
                        $result['errors'][] = $syntaxCheck['error'];
                    }
                }
                // Check file permissions
                if (! is_readable($templatePath)) {
                    $result['warnings'][] = 'File is not readable';
                }
                if (! is_writable($templatePath)) {
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
                'message' => 'Validation failed: '.$e->getMessage(),
            ], 500);
        }
    }
    /**
     * Validate PHP syntax.
     */
    private function validatePHPSyntax($code)
    {
        // Create a temporary file for syntax checking
        $tempFile = tempnam(sys_get_temp_dir(), 'php_syntax_check');
        file_put_contents($tempFile, $code);
    // Run PHP syntax check using Symfony Process (safer than shell_exec)
    $process = new Process(['php', '-l', $tempFile]);
    $process->run();
    $output = $process->getOutput().$process->getErrorOutput();
        // Clean up
        unlink($tempFile);
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
     * @param  ProgrammingLanguageTemplateRequest  $request  The validated request containing template file
     * @param  ProgrammingLanguage  $programming_language  The programming language to upload template for
     *
     * @return JsonResponse JSON response with upload result
     *
     * @throws \Exception When file operations fail
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function uploadTemplate(
        ProgrammingLanguageTemplateRequest $request,
        ProgrammingLanguage $programming_language,
    ): JsonResponse {
        try {
            $file = $request->file('template_file');
            $templateDir = resource_path('templates/licenses');
            // Create directory if it doesn't exist
            if (! is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }
            $filename = $programming_language->slug.'.php';
            $file->move($templateDir, $filename);
            return response()->json([
                'success' => true,
                'message' => 'Template file uploaded successfully',
                'file_path' => $templateDir.'/'.$filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload template file: '.$e->getMessage(),
            ], 500);
        }
    }
    /**
     * Create template file from textarea content with enhanced security.
     *
     * Creates a template file from provided content for a specific
     * programming language with proper validation and security measures.
     *
     * @param  ProgrammingLanguageContentRequest  $request  The validated request containing template content
     * @param  ProgrammingLanguage  $programming_language  The programming language to create template for
     *
     * @return JsonResponse JSON response with creation result
     *
     * @throws \Exception When file operations fail
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function createTemplateFile(
        ProgrammingLanguageAdvancedRequest $request,
        ProgrammingLanguage $programming_language,
    ): JsonResponse {
        try {
            $templateDir = resource_path('templates/licenses');
            // Create directory if it doesn't exist
            if (! is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }
            $filename = $programming_language->slug.'.php';
            $filePath = $templateDir.'/'.$filename;
            file_put_contents($filePath, $request->template_content);
            return response()->json([
                'success' => true,
                'message' => 'Template file created successfully',
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template file: '.$e->getMessage(),
            ], 500);
        }
    }
    /**
     * Export programming languages to CSV format with comprehensive data.
     *
     * Generates a CSV file containing all programming languages with their
     * configuration details for administrative purposes.
     *
     * @return \Illuminate\Http\Response The CSV download response
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function export(): \Illuminate\Http\Response
    {
        $languages = ProgrammingLanguage::orderBy('sort_order')->orderBy('name')->get();
        $filename = 'programming_languages_'.date('Y-m-d_H-i-s').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $callback = function () use ($languages) {
            $file = fopen('php://output', 'w');
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
                    $language->created_at->format('Y-m-d H:i:s'),
                    $language->updated_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    /**
     * Get template content for a programming language with file information.
     *
     * Retrieves the template content for a specific programming language
     * along with file metadata including size and modification date.
     *
     * @param  ProgrammingLanguage  $programming_language  The programming language to get template content for
     *
     * @return JsonResponse JSON response with template content or error
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function getTemplateContent(ProgrammingLanguage $programming_language): JsonResponse
    {
        $templatePath = resource_path("templates/licenses/{$programming_language->slug}.blade.php");
        if (file_exists($templatePath)) {
            $content = file_get_contents($templatePath);
            return response()->json([
                'success' => true,
                'content' => $content,
                'file_path' => $templatePath,
                'file_size' => filesize($templatePath),
                'last_modified' => date('Y-m-d H:i:s', filemtime($templatePath)),
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Template file not found',
            'file_path' => $templatePath,
        ], 404);
    }
}
