<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateTestLicenseRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Services\LicenseGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Product Controller.
 *
 * This controller handles comprehensive product management functionality including
 * CRUD operations, Envato API integration, file management, and license generation.
 *
 * Features:
 * - Product CRUD operations with validation
 * - Envato API integration for product data
 * - File upload and management (images, gallery, product files)
 * - Integration file generation for different programming languages
 * - Test license generation
 * - Knowledge base integration
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (input validation, file upload security)
 * - Proper logging for errors and warnings only
 */
class ProductController extends Controller
{
    protected LicenseGeneratorService $licenseGenerator;

    /**
     * Constructor.
     */
    public function __construct(LicenseGeneratorService $licenseGenerator)
    {
        $this->licenseGenerator = $licenseGenerator;
    }

    /**
     * Get product data from Envato API.
     */
    public function getEnvatoProductData(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|integer|min:1',
        ]);
        $itemId = $request->input('item_id');
        try {
            $envatoService = app(\App\Services\EnvatoService::class);
            $itemData = $envatoService->getItemInfo(is_numeric($itemId) ? (int)$itemId : 0);
            if (! $itemData) {
                return response()->json([
                    'success' => false,
                    'message' => trans('app.Unable to fetch product data from Envato'),
                ], 404);
            }
            // Extract relevant data from Envato API response
            $productData = [
                'success' => true,
                'data' => [
                    'envato_item_id' => $itemData['id'] ?? null,
                    'purchase_url_envato' => $itemData['url'] ?? null,
                    'purchase_url_buy' => $itemData['url'] ?? null, // Same as purchase URL for now
                    'support_days' => $this->calculateSupportDays($itemData),
                    'version' => $itemData['version'] ?? null,
                    'price' => isset($itemData['price_cents']) && is_numeric($itemData['price_cents']) ? ($itemData['price_cents'] / 100) : null,
                    'name' => $itemData['name'] ?? null,
                    'description' => $itemData['description'] ?? null,
                ],
            ];

            return response()->json($productData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('app.Error fetching product data: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's Envato items for selection.
     */
    public function getEnvatoUserItems(Request $request): JsonResponse
    {
        try {
            $envatoService = app(\App\Services\EnvatoService::class);
            $settings = $envatoService->getEnvatoSettings();
            if (empty($settings['username'])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('app.Envato username not configured'),
                ], 400);
            }
            $username = $settings['username'];
            $userItems = $envatoService->getUserItems(is_string($username) ? $username : '');
            if (! $userItems || ! isset($userItems['matches'])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('app.Unable to fetch user items from Envato'),
                ], 404);
            }
            /**
 * @var array<int, array<string, mixed>> $matches
*/
            $matches = $userItems['matches'];
            $items = collect($matches)->map(function (array $item): array {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'url' => $item['url'],
                    'price' => isset($item['price_cents']) && is_numeric($item['price_cents']) ? ($item['price_cents'] / 100) : 0,
                    'rating' => $item['rating'] ?? null,
                    'sales' => $item['number_of_sales'] ?? 0,
                ];
            });

            return response()->json([
                'success' => true,
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('app.Error fetching user items: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate support days from Envato item data.
     */
    /**
 * @param array<mixed, mixed> $itemData
*/
    private function calculateSupportDays(array $itemData): int
    {
        // Envato typically provides 6 months support for most items
        // This can be adjusted based on the actual API response
        if (isset($itemData['attributes']) && is_array($itemData['attributes'])) {
            foreach ($itemData['attributes'] as $attribute) {
                if (is_array($attribute) && isset($attribute['name']) && $attribute['name'] === 'support') {
                    // Parse support duration (e.g., "6 months", "1 year")
                    $value = strtolower(is_string($attribute['value'] ?? null) ? $attribute['value'] : '');
                    if (strpos($value, 'month') !== false) {
                        preg_match('/(\d+)/', $value, $matches);

                        return isset($matches[1]) ? (int)$matches[1] * 30 : 180;
                    } elseif (strpos($value, 'year') !== false) {
                        preg_match('/(\d+)/', $value, $matches);

                        return isset($matches[1]) ? (int)$matches[1] * 365 : 365;
                    }
                }
            }
        }

        // Default to 6 months (180 days) if not specified
        return 180;
    }

    /**
     * Get integration code template for product.
     */
    private function getIntegrationCodeTemplate(Product $product, string $apiUrl): string
    {
        // Return a minimal placeholder integration file to avoid complex embedded templates here.
        return "<?php\n// Integration placeholder for {$product->slug}\n// API: {$apiUrl}\n";
    }

    /**
     * Display a listing of the resource (admin).
     */
    public function index(): View
    {
        $productsQuery = Product::with(['category', 'programmingLanguage']);
        // Apply search filter (support both 'q' and 'search')
        $search = request('q', request('search'));
        if (! empty($search)) {
            $productsQuery->where(function ($query) use ($search) {
                $searchStr = is_string($search) ? $search : '';
                $query->where('name', 'like', "%{$searchStr}%")
                    ->orWhere('description', 'like', "%{$searchStr}%");
            });
        }
        // Show all products without any category filter
        // Removed category filter to display all products regardless of category assignment
        // Apply language filter
        if (request('language')) {
            $productsQuery->where('programming_language', request('language'));
        }
        // Apply price filter
        $priceFilter = request('price_filter');
        if ($priceFilter === 'free') {
            $productsQuery->where('price', 0);
        } elseif ($priceFilter === 'paid') {
            $productsQuery->where('price', '>', 0);
        }
        // Apply sorting
        $sort = request('sort', 'name');
        switch ($sort) {
            case 'price_low':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_high':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'newest':
                $productsQuery->orderBy('created_at', 'desc');
                break;
            default:
                $productsQuery->orderBy('name', 'asc');
        }
        $products = $productsQuery->paginate(10)->withQueryString();
        $categories = \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->get();
        $programmingLanguages = \App\Models\ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get();
        // Provide all products collection for grouped/category displays in the admin index
        $allProducts = Product::with(['category', 'programmingLanguage'])->get();

        return view('admin.products.index', ['products' => $products, 'categories' => $categories, 'programmingLanguages' => $programmingLanguages, 'allProducts' => $allProducts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->get();
        $programmingLanguages = \App\Models\ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get();
        $kbCategories = KbCategory::where('is_published', true)->orderBy('name')->get(['id', 'name', 'slug']);
        $kbArticles = KbArticle::where('is_published', true)
            ->with('category:id, name')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'kb_category_id']);

        return view('admin.products.create', [
            'categories' => $categories,
            'programmingLanguages' => $programmingLanguages,
            'kbCategories' => $kbCategories,
            'kbArticles' => $kbArticles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ProductRequest  $request
     *
     * @throws \Exception When database operations fail
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Convert requires_domain to boolean if present
            if (isset($validated['requires_domain'])) {
                $validated['requires_domain'] = (bool)$validated['requires_domain'];
            }
            $validated['slug'] = $validated['slug'] ?? Str::slug(is_string($validated['name'] ?? null) ? $validated['name'] : '');
            // Handle main image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }
            // Handle gallery images
            if ($request->hasFile('gallery_images')) {
                $galleryPaths = [];
                foreach ($request->file('gallery_images') as $file) {
                    $galleryPaths[] = $file->store('products/gallery', 'public');
                }
                $validated['gallery_images'] = $galleryPaths;
            }
            // Handle lifetime renewal period - set extended_supported_until to null for lifetime
            if (isset($validated['renewal_period']) && $validated['renewal_period'] === 'lifetime') {
                $validated['extended_supported_until'] = null;
            }
            $product = Product::create($validated);
            // Handle product files upload
            if ($request->hasFile('product_files')) {
                try {
                    $productFileService = app(\App\Services\ProductFileService::class);
                    foreach ($request->file('product_files') as $file) {
                        if ($file->isValid()) {
                            $productFileService->uploadFile($product, $file);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Product file upload error: ' . $e->getMessage());

                    return back()->with('error', 'Error uploading files: ' . $e->getMessage())->withInput();
                }
            }
            // Generate integration file
            $this->generateIntegrationFile($product);
            DB::commit();

            return redirect()->route('admin.products.edit', $product)->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation error: ' . $e->getMessage());

            return back()->with('error', 'Error creating product: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource (admin).
     */
    public function show(Product $product): View
    {
        // Load the product with its relationships
        $product->load(['category', 'programmingLanguage', 'updates']);

        return view('admin.products.show', ['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ProductRequest  $request
     *
     * @throws \Exception When database operations fail
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Convert requires_domain to boolean if present
            if (isset($validated['requires_domain'])) {
                $validated['requires_domain'] = (bool)$validated['requires_domain'];
            }
            // Handle main image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            }
            // Handle gallery images
            if ($request->hasFile('gallery_images')) {
                $galleryPaths = [];
                foreach ($request->file('gallery_images') as $file) {
                    $galleryPaths[] = $file->store('products/gallery', 'public');
                }
                $validated['gallery_images'] = $galleryPaths;
            }
            // Handle lifetime renewal period - set extended_supported_until to null for lifetime
            if (isset($validated['renewal_period']) && $validated['renewal_period'] === 'lifetime') {
                $validated['extended_supported_until'] = null;
            }
            $product->update($validated);
            // Handle product files upload
            if ($request->hasFile('product_files')) {
                try {
                    $productFileService = app(\App\Services\ProductFileService::class);
                    foreach ($request->file('product_files') as $file) {
                        if ($file->isValid()) {
                            $productFileService->uploadFile($product, $file);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Product file upload error: ' . $e->getMessage());

                    return back()->with('error', 'Error uploading files: ' . $e->getMessage())->withInput();
                }
            }
            // Regenerate file if programming language or envato settings changed
            if ($product->wasChanged(['programming_language', 'envato_item_id', 'name', 'slug'])) {
                $this->generateIntegrationFile($product);
            }
            DB::commit();

            return back()->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update error: ' . $e->getMessage());

            return back()->with('error', 'Error updating product: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            // Delete integration file if exists
            $filePath = "integration/{$product->slug}.php";
            if (Storage::disk('public')->exists($filePath) === true) {
                Storage::disk('public')->delete($filePath);
            }
            $product->delete();
            DB::commit();

            return redirect()->route('admin.products.index')->with('success', 'Product deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion error: ' . $e->getMessage());

            return back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    /**
     * Get product data for license forms (AJAX endpoint).
     */
    public function getProductData(Product $product): JsonResponse
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'license_type' => $product->license_type,
            'duration_days' => $product->duration_days,
            'support_days' => $product->support_days,
            'price' => $product->price,
            'renewal_price' => $product->renewal_price,
            'renewal_period' => $product->renewal_period,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $categories = \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->get();
        $programmingLanguages = \App\Models\ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get();
        // Load product files
        $product->load('files');

        return view('admin.products.edit', ['product' => $product, 'categories' => $categories, 'programmingLanguages' => $programmingLanguages]);
    }

    /**
     * Generate integration file for a product.
     */
    private function generateIntegrationFile(Product $product): string
    {
        try {
            // Delete old integration file if exists
            $oldFilePath = "integration/{$product->slug}.php";
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }
            // Delete any old files with different extensions based on programming language
            $programmingLanguage = $product->programmingLanguage;
            if ($programmingLanguage) {
                $extensions = $this->getFileExtensionsForLanguage($programmingLanguage->slug);
                foreach ($extensions as $ext) {
                    $oldFileWithExt = "integration/{$product->slug}.{(is_string($ext) ? $ext : (string)$ext)}";
                    if (Storage::disk('public')->exists($oldFileWithExt)) {
                        Storage::disk('public')->delete($oldFileWithExt);
                    }
                }
            }
            // Use the new LicenseGeneratorService
            $filePath = $this->licenseGenerator->generateLicenseFile($product);

            return $filePath;
        } catch (\Exception $e) {
            // Fallback to old method if new service fails
            $apiDomain = rtrim(is_string(config('app.url')) ? config('app.url') : '', '/');
            $verificationEndpoint = is_string(config('license.verification_endpoint', '/api/license/verify')) ? config('license.verification_endpoint', '/api/license/verify') : '/api/license/verify';
            $apiUrl = $apiDomain . '/' . ltrim($verificationEndpoint, '/');
            $integrationCode = $this->getIntegrationCodeTemplate($product, $apiUrl);
            // Save to storage/app/public/integration/
            $filePath = "integration/{$product->slug}.php";
            Storage::disk('public')->put($filePath, $integrationCode);
            // Update product with integration file path
            $product->update([
                'integration_file_path' => $filePath,
            ]);

            return $filePath;
        }
    }

    /**
     * Download file for a product.
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadIntegration(Product $product)
    {
        if (! $product->integration_file_path || ! Storage::disk('public')->exists($product->integration_file_path)) {
            return redirect()->back()->with('error', 'Integration file not found. Please regenerate it.');
        }

        return Storage::disk('public')->download($product->integration_file_path, "{$product->slug}.php");
    }

    /**
     * Regenerate file for a product.
     */
    public function regenerateIntegration(Product $product): RedirectResponse
    {
        try {
            // Delete old integration file if exists
            $oldFilePath = "integration/{$product->slug}.php";
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }
            // Delete any old files with different extensions based on programming language
            $programmingLanguage = $product->programmingLanguage;
            if ($programmingLanguage) {
                $extensions = $this->getFileExtensionsForLanguage($programmingLanguage->slug);
                foreach ($extensions as $ext) {
                    $oldFileWithExt = "integration/{$product->slug}.{(is_string($ext) ? $ext : (string)$ext)}";
                    if (Storage::disk('public')->exists($oldFileWithExt)) {
                        Storage::disk('public')->delete($oldFileWithExt);
                    }
                }
            }
            // Generate new integration file
            $this->generateIntegrationFile($product);

            return redirect()->back()->with('success', 'Integration file regenerated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to Regenerate file: ' . $e->getMessage());
        }
    }

    /**
     * Get file extensions for programming language.
     */
    /**
 * @return array<string>
*/
    private function getFileExtensionsForLanguage(string $languageSlug): array
    {
        $extensions = [
            'php' => ['php'],
            'laravel' => ['php'],
            'javascript' => ['js'],
            'python' => ['py'],
            'java' => ['java'],
            'csharp' => ['cs'],
            'cpp' => ['cpp', 'h'],
            'wordpress' => ['php'],
            'react' => ['js', 'jsx'],
            'angular' => ['ts'],
            'nodejs' => ['js'],
            'vuejs' => ['js', 'vue'],
            'go' => ['go'],
            'swift' => ['swift'],
            'typescript' => ['ts'],
            'kotlin' => ['kt'],
            'c' => ['c', 'h'],
            'html-css' => ['html', 'css'],
            'flask' => ['py'],
            'django' => ['py'],
            'expressjs' => ['js'],
            'ruby-on-rails' => ['rb'],
            'spring-boot' => ['java'],
            'symfony' => ['php'],
            'aspnet' => ['cs'],
            'html' => ['html'],
            'ruby' => ['rb'],
        ];

        return $extensions[$languageSlug] ?? ['php'];
    }

    /**
     * Generate a test license for the product.
     *
     * @throws \Exception When database operations fail
     */
    public function generateTestLicense(GenerateTestLicenseRequest $request, Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Find or create user
            $user = \App\Models\User::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'] ?? 'Test User',
                    'email' => $validated['email'],
                    'password' => bcrypt('password123'), // Default password for test users
                    'email_verified_at' => now(),
                ],
            );
            // Generate unique purchase code
            $purchaseCode = 'TEST-' . strtoupper(Str::random(16));
            // Create license (license_key will be automatically set to same value as purchase_code)
            $license = License::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'purchase_code' => $purchaseCode,
                'status' => 'active',
                'license_type' => 'regular',
                'support_expires_at' => now()->addDays($product->support_days),
                'license_expires_at' => now()->addYear(),
            ]);
            // Add domain
            $license->domains()->create([
                'domain' => $validated['domain'],
            ]);
            DB::commit();

            return redirect()->back()->with('success', "Test license generated: {$purchaseCode}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Test license generation error: ' . $e->getMessage());

            return back()->with('error', 'Error generating test license: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show license verification logs for a product.
     */
    public function logs(Product $product): View
    {
        $logs = LicenseLog::with(['license'])
            ->whereHas('license', fn ($q) => $q->where('product_id', $product->id))
            ->orWhere('serial', 'like', '%TEST-%') // For test licenses without license_id
            ->latest()
            ->paginate(50);

        return view('admin.products.logs', ['product' => $product, 'logs' => $logs]);
    }

    /**
     * Get KB categories and articles for product form.
     */
    public function getKbData(): JsonResponse
    {
        $categories = KbCategory::where('is_published', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
        $articles = KbArticle::where('is_published', true)
            ->with('category:id, name')
            ->orderBy('title')
            ->get([
                'id', 'title', 'slug', 'kb_category_id',
            ]);

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }

    /**
     * Get KB articles for a specific category.
     */
    public function getKbArticles(int $categoryId): JsonResponse
    {
        $articles = KbArticle::where('kb_category_id', $categoryId)
            ->where('is_published', true)
            ->with('category:id, name')
            ->orderBy('title')
            ->get([
                'id', 'title', 'slug', 'kb_category_id',
            ]);

        return response()->json([
            'success' => true,
            'articles' => $articles,
        ]);
    }
}
