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
use App\Services\EnvatoProductService;
use App\Services\LicenseGeneratorService;
use App\Services\ProductApiService;
use App\Services\ProductIntegrationService;
use App\Services\ProductKbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin Product Controller
 * Handles product management operations
 */
class ProductController extends Controller
{
    public function __construct(
        private LicenseGeneratorService $licenseGenerator,
        private ProductApiService $productApiService,
        private ProductIntegrationService $productIntegrationService
    ) {
    }

    /**
     * Handle API requests for product operations
     */
    public function api(Request $request): JsonResponse
    {
        return $this->productApiService->handleApiRequest($request);
    }



    /**
     * Display products listing
     */
    public function index(): View
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

        $priceFilter = request('price_filter');
        if ($priceFilter === 'free') {
            $query->where('price', 0);
        } elseif ($priceFilter === 'paid') {
            $query->where('price', '>', 0);
        }

        $sort = request('sort', 'name');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc')
        };

        return view('admin.products.index', [
            'products' => $query->paginate(10)->withQueryString(),
            'categories' => \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'programmingLanguages' => \App\Models\ProgrammingLanguage::where('is_active', true)
                ->orderBy('sort_order')->get(),
            'allProducts' => Product::with(['category', 'programmingLanguage'])->get()
        ]);
    }

    /**
     * Show create product form
     */
    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'programmingLanguages' => \App\Models\ProgrammingLanguage::where('is_active', true)
                ->orderBy('sort_order')->get(),
            'kbCategories' => KbCategory::where('is_published', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'kbArticles' => KbArticle::where('is_published', true)
                ->with('category:id, name')
                ->orderBy('title')
                ->get(['id', 'title', 'slug', 'kb_category_id']),
        ]);
    }

    /**
     * Store new product
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            if (isset($data['requires_domain'])) {
                $data['requires_domain'] = (bool)$data['requires_domain'];
            }

            $data['slug'] = $data['slug'] ?? Str::slug($data['name'] ?? '');

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            if ($request->hasFile('gallery_images')) {
                $data['gallery_images'] = collect($request->file('gallery_images'))
                    ->map(fn($file) => $file->store('products/gallery', 'public'))
                    ->toArray();
            }

            if (isset($data['renewal_period']) && $data['renewal_period'] === 'lifetime') {
                $data['extended_supported_until'] = null;
            }

            $product = Product::create($data);

            if ($request->hasFile('product_files')) {
                $productFileService = app(\App\Services\ProductFileService::class);
                foreach ($request->file('product_files') as $file) {
                    if ($file->isValid()) {
                        $productFileService->uploadFile($product, $file);
                    }
                }
            }

            $this->productIntegrationService->generateIntegrationFile($product);
            DB::commit();

            return redirect()->route('admin.products.edit', $product)->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation error: ' . $e->getMessage());
            return back()->with('error', 'Error creating product: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show product details
     */
    public function show(Product $product): View
    {
        $product->load(['category', 'programmingLanguage', 'updates']);
        return view('admin.products.show', ['product' => $product]);
    }

    /**
     * Update product
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            if (isset($data['requires_domain'])) {
                $data['requires_domain'] = (bool)$data['requires_domain'];
            }

            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            if ($request->hasFile('gallery_images')) {
                $data['gallery_images'] = collect($request->file('gallery_images'))
                    ->map(fn($file) => $file->store('products/gallery', 'public'))
                    ->toArray();
            }

            if (isset($data['renewal_period']) && $data['renewal_period'] === 'lifetime') {
                $data['extended_supported_until'] = null;
            }

            $product->update($data);

            if ($request->hasFile('product_files')) {
                $productFileService = app(\App\Services\ProductFileService::class);
                foreach ($request->file('product_files') as $file) {
                    if ($file->isValid()) {
                        $productFileService->uploadFile($product, $file);
                    }
                }
            }

            if ($product->wasChanged(['programming_language', 'envato_item_id', 'name', 'slug'])) {
                $this->productIntegrationService->generateIntegrationFile($product);
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
     * Delete product
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $filePath = "integration/{$product->slug}.php";
            if (Storage::disk('public')->exists($filePath)) {
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
     * Show edit product form
     */
    public function edit(Product $product): View
    {
        $product->load('files');
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'programmingLanguages' => \App\Models\ProgrammingLanguage::where('is_active', true)
                ->orderBy('sort_order')->get()
        ]);
    }


    /**
     * Download integration file
     */
    public function downloadIntegration(Product $product)
    {
        return $this->productIntegrationService->downloadIntegration($product);
    }

    /**
     * Regenerate integration file
     */
    public function regenerateIntegration(Product $product): RedirectResponse
    {
        return $this->productIntegrationService->regenerateIntegration($product);
    }


    /**
     * Generate test license for product
     */
    public function generateTestLicense(GenerateTestLicenseRequest $request, Product $product): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            $user = \App\Models\User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'] ?? 'Test User',
                    'email' => $data['email'],
                    'password' => bcrypt('password123'),
                    'email_verified_at' => now(),
                ]
            );

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
            DB::commit();

            return redirect()->back()->with('success', "Test license generated: {$purchaseCode}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Test license generation error: ' . $e->getMessage());
            return back()->with('error', 'Error generating test license: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show license verification logs
     */
    public function logs(Product $product): View
    {
        $logs = LicenseLog::with(['license'])
            ->whereHas('license', fn ($q) => $q->where('product_id', $product->id))
            ->orWhere('serial', 'like', '%TEST-%')
            ->latest()
            ->paginate(50);

        return view('admin.products.logs', ['product' => $product, 'logs' => $logs]);
    }
}
