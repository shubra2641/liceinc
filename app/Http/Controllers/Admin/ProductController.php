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

class ProductController extends Controller
{
    public function __construct(private LicenseGeneratorService $licenseGenerator)
    {
    }

    // Public Methods
    public function index(): View
    {
        return view('admin.products.index', $this->getIndexData());
    }

    public function create(): View
    {
        return view('admin.products.create', $this->getCreateData());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        return $this->handleProductOperation($request, 'create');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'programmingLanguage', 'updates']);
        return view('admin.products.show', ['product' => $product]);
    }

    public function edit(Product $product): View
    {
        $product->load('files');
        return view('admin.products.edit', $this->getEditData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        return $this->handleProductOperation($request, 'update', $product);
    }

    public function destroy(Product $product): RedirectResponse
    {
        return $this->handleDelete($product);
    }

    // API Methods
    public function getEnvatoProductData(Request $request): JsonResponse
    {
        $request->validate(['item_id' => 'required|integer|min:1']);

        try {
            $envatoService = app(\App\Services\EnvatoService::class);
            $itemData = $envatoService->getItemInfo((int)$request->input('item_id'));

            if (!$itemData) {
                return $this->jsonError('Unable to fetch product data from Envato', 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatEnvatoData($itemData),
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Error fetching product data: ' . $e->getMessage(), 500);
        }
    }

    public function getEnvatoUserItems(Request $request): JsonResponse
    {
        try {
            $envatoService = app(\App\Services\EnvatoService::class);
            $settings = $envatoService->getEnvatoSettings();

            if (empty($settings['username'])) {
                return $this->jsonError('Envato username not configured', 400);
            }

            $userItems = $envatoService->getUserItems($settings['username']);

            if (!$userItems || !isset($userItems['matches'])) {
                return $this->jsonError('Unable to fetch user items from Envato', 404);
            }

            return response()->json([
                'success' => true,
                'items' => $this->formatUserItems($userItems['matches'])
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Error fetching user items: ' . $e->getMessage(), 500);
        }
    }

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

    public function getKbData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'categories' => $this->getKbCategories(),
            'articles' => $this->getKbArticles(),
        ]);
    }

    public function getKbArticles(int $categoryId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'articles' => KbArticle::where('kb_category_id', $categoryId)
                ->where('is_published', true)
                ->with('category:id, name')
                ->orderBy('title')
                ->get(['id', 'title', 'slug', 'kb_category_id']),
        ]);
    }

    // Integration Methods
    public function downloadIntegration(Product $product)
    {
        if (!$product->integration_file_path || !Storage::disk('public')->exists($product->integration_file_path)) {
            return redirect()->back()->with('error', 'Integration file not found. Please regenerate it.');
        }
        return Storage::disk('public')->download($product->integration_file_path, "{$product->slug}.php");
    }

    public function regenerateIntegration(Product $product): RedirectResponse
    {
        try {
            $this->deleteIntegrationFile($product);
            $this->generateIntegrationFile($product);
            return redirect()->back()->with('success', 'Integration file regenerated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to Regenerate file: ' . $e->getMessage());
        }
    }

    // License Methods
    public function generateTestLicense(GenerateTestLicenseRequest $request, Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            $user = $this->createTestUser($data);
            $license = $this->createTestLicense($product, $user, $data);
            
            DB::commit();
            return redirect()->back()->with('success', "Test license generated: {$license->purchase_code}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Test license generation error: ' . $e->getMessage());
            return back()->with('error', 'Error generating test license: ' . $e->getMessage())->withInput();
        }
    }

    public function logs(Product $product): View
    {
        $logs = $this->getLicenseLogs($product);
        return view('admin.products.logs', ['product' => $product, 'logs' => $logs]);
    }

    // Helper Methods
    private function getIndexData(): array
    {
        $query = $this->buildProductQuery();
        
        return [
            'products' => $query->paginate(10)->withQueryString(),
            'categories' => $this->getCategories(),
            'programmingLanguages' => $this->getProgrammingLanguages(),
            'allProducts' => Product::with(['category', 'programmingLanguage'])->get()
        ];
    }

    private function getCreateData(): array
    {
        return [
            'categories' => $this->getCategories(),
            'programmingLanguages' => $this->getProgrammingLanguages(),
            'kbCategories' => $this->getKbCategories(),
            'kbArticles' => $this->getKbArticles(),
        ];
    }

    private function getEditData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => $this->getCategories(),
            'programmingLanguages' => $this->getProgrammingLanguages()
        ];
    }

    private function buildProductQuery()
    {
        $query = Product::with(['category', 'programmingLanguage']);

        if ($search = request('q', request('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($language = request('language')) {
            $query->where('programming_language', $language);
        }

        $this->applyPriceFilter($query);
        $this->applySorting($query);

        return $query;
    }

    private function applyPriceFilter($query): void
    {
        $priceFilter = request('price_filter');
        if ($priceFilter === 'free') {
            $query->where('price', 0);
        } elseif ($priceFilter === 'paid') {
            $query->where('price', '>', 0);
        }
    }

    private function applySorting($query): void
    {
        $sort = request('sort', 'name');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc')
        };
    }

    private function handleProductOperation(ProductRequest $request, string $operation, Product $product = null): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $data = $this->prepareProductData($request);
            
            if ($operation === 'create') {
                $product = Product::create($data);
                $this->handleFileUploads($request, $product);
                $this->generateIntegrationFile($product);
                DB::commit();
                return redirect()->route('admin.products.edit', $product)->with('success', 'Product created successfully');
            } else {
                $this->handleImageUpdate($request, $product, $data);
                $this->handleGalleryUpdate($request, $data);
                $this->handleRenewalPeriod($data);
                
                $product->update($data);
                $this->handleFileUploads($request, $product);
                
                if ($this->shouldRegenerateIntegration($product, $data)) {
                    $this->generateIntegrationFile($product);
                }
                
                DB::commit();
                return back()->with('success', 'Product updated successfully');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Product {$operation} error: " . $e->getMessage());
            return back()->with('error', "Error {$operation}ing product: " . $e->getMessage())->withInput();
        }
    }

    private function handleDelete(Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $this->deleteIntegrationFile($product);
            $product->delete();
            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Product deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    private function prepareProductData(ProductRequest $request): array
    {
        $data = $request->validated();
        
        if (isset($data['requires_domain'])) {
            $data['requires_domain'] = (bool)$data['requires_domain'];
        }
        
        $data['slug'] = $data['slug'] ?? Str::slug($data['name'] ?? '');
        
        return $data;
    }

    private function handleFileUploads(ProductRequest $request, Product $product): void
    {
        if ($request->hasFile('product_files')) {
            $productFileService = app(\App\Services\ProductFileService::class);
            foreach ($request->file('product_files') as $file) {
                if ($file->isValid()) {
                    $productFileService->uploadFile($product, $file);
                }
            }
        }
    }

    private function handleImageUpdate(ProductRequest $request, Product $product, array &$data): void
    {
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }
    }

    private function handleGalleryUpdate(ProductRequest $request, array &$data): void
    {
        if ($request->hasFile('gallery_images')) {
            $data['gallery_images'] = collect($request->file('gallery_images'))
                ->map(fn($file) => $file->store('products/gallery', 'public'))
                ->toArray();
        }
    }

    private function handleRenewalPeriod(array &$data): void
    {
        if (isset($data['renewal_period']) && $data['renewal_period'] === 'lifetime') {
            $data['extended_supported_until'] = null;
        }
    }

    private function shouldRegenerateIntegration(Product $product, array $data): bool
    {
        return $product->wasChanged(['programming_language', 'envato_item_id', 'name', 'slug']);
    }

    private function generateIntegrationFile(Product $product): string
    {
        try {
            $this->deleteIntegrationFile($product);
            return $this->licenseGenerator->generateLicenseFile($product);
        } catch (\Exception $e) {
            return $this->createFallbackIntegrationFile($product);
        }
    }

    private function deleteIntegrationFile(Product $product): void
    {
        $filePath = "integration/{$product->slug}.php";
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        if ($programmingLanguage = $product->programmingLanguage) {
            foreach ($this->getFileExtensionsForLanguage($programmingLanguage->slug) as $ext) {
                $oldFileWithExt = "integration/{$product->slug}.{$ext}";
                if (Storage::disk('public')->exists($oldFileWithExt)) {
                    Storage::disk('public')->delete($oldFileWithExt);
                }
            }
        }
    }

    private function createFallbackIntegrationFile(Product $product): string
    {
        $apiDomain = rtrim(config('app.url', ''), '/');
        $verificationEndpoint = config('license.verification_endpoint', '/api/license/verify');
        $apiUrl = $apiDomain . '/' . ltrim($verificationEndpoint, '/');
        
        $integrationCode = "<?php\ndeclare(strict_types=1);\n// Integration placeholder for {$product->slug}\n// API: {$apiUrl}\n";
        $filePath = "integration/{$product->slug}.php";
        
        Storage::disk('public')->put($filePath, $integrationCode);
        $product->update(['integration_file_path' => $filePath]);
        
        return $filePath;
    }

    private function getFileExtensionsForLanguage(string $languageSlug): array
    {
        $extensions = [
            'php' => ['php'], 'laravel' => ['php'], 'wordpress' => ['php'], 'symfony' => ['php'],
            'javascript' => ['js'], 'react' => ['js', 'jsx'], 'nodejs' => ['js'], 'vuejs' => ['js', 'vue'], 'expressjs' => ['js'],
            'python' => ['py'], 'flask' => ['py'], 'django' => ['py'],
            'java' => ['java'], 'spring-boot' => ['java'],
            'csharp' => ['cs'], 'aspnet' => ['cs'],
            'cpp' => ['cpp', 'h'], 'c' => ['c', 'h'],
            'angular' => ['ts'], 'typescript' => ['ts'],
            'go' => ['go'], 'swift' => ['swift'], 'kotlin' => ['kt'],
            'ruby' => ['rb'], 'ruby-on-rails' => ['rb'],
            'html' => ['html'], 'html-css' => ['html', 'css'],
        ];
        
        return $extensions[$languageSlug] ?? ['php'];
    }

    private function createTestUser(array $data): \App\Models\User
    {
        return \App\Models\User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'] ?? 'Test User',
                'email' => $data['email'],
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
    }

    private function createTestLicense(Product $product, \App\Models\User $user, array $data): License
    {
        $purchaseCode = 'TEST-' . strtoupper(Str::random(16));

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'purchase_code' => $purchaseCode,
            'status' => 'active',
            'license_type' => 'regular',
            'support_expires_at' => now()->addDays($product->support_days),
            'license_expires_at' => now()->addYear(),
        ]);

        $license->domains()->create(['domain' => $data['domain']]);
        
        return $license;
    }

    private function getLicenseLogs(Product $product)
    {
        return LicenseLog::with(['license'])
            ->whereHas('license', fn ($q) => $q->where('product_id', $product->id))
            ->orWhere('serial', 'like', '%TEST-%')
            ->latest()
            ->paginate(50);
    }

    // Data Methods
    private function getCategories()
    {
        return \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->get();
    }

    private function getProgrammingLanguages()
    {
        return \App\Models\ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get();
    }

    private function getKbCategories()
    {
        return KbCategory::where('is_published', true)->orderBy('name')->get(['id', 'name', 'slug']);
    }

    private function getKbArticles()
    {
        return KbArticle::where('is_published', true)->with('category:id, name')->orderBy('title')->get(['id', 'title', 'slug', 'kb_category_id']);
    }

    // Envato Methods
    private function jsonError(string $message, int $code = 400): JsonResponse
    {
        return response()->json(['success' => false, 'message' => trans("app.{$message}")], $code);
    }

    private function formatEnvatoData(array $itemData): array
    {
        return [
            'envato_item_id' => $itemData['id'] ?? null,
            'purchase_url_envato' => $itemData['url'] ?? null,
            'purchase_url_buy' => $itemData['url'] ?? null,
            'support_days' => $this->calculateSupportDays($itemData),
            'version' => $itemData['version'] ?? null,
            'price' => isset($itemData['price_cents']) ? ($itemData['price_cents'] / 100) : null,
            'name' => $itemData['name'] ?? null,
            'description' => $itemData['description'] ?? null,
        ];
    }

    private function formatUserItems(array $matches): array
    {
        return collect($matches)->map(function (array $item): array {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'url' => $item['url'],
                'price' => isset($item['price_cents']) ? ($item['price_cents'] / 100) : 0,
                'rating' => $item['rating'] ?? null,
                'sales' => $item['number_of_sales'] ?? 0,
            ];
        })->toArray();
    }

    private function calculateSupportDays(array $itemData): int
    {
        if (isset($itemData['attributes']) && is_array($itemData['attributes'])) {
            foreach ($itemData['attributes'] as $attribute) {
                if (is_array($attribute) && isset($attribute['name']) && $attribute['name'] === 'support') {
                    $value = strtolower($attribute['value'] ?? '');
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
        return 180;
    }
}