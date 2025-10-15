<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductSearchRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProgrammingLanguage;
use App\Services\ProductFileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'user', 'verified'])->only(['index', 'show']);
    }

    public function index(ProductSearchRequest $request): View
    {
        return $this->publicIndex($request);
    }

    public function publicIndex(ProductSearchRequest $request = null): View
    {
        $query = Product::with(['category', 'programmingLanguage'])->where('is_active', true);

        $this->applySearchFilter($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applyLanguageFilter($query, $request);
        $this->applyPriceFilter($query, $request);
        $this->applySorting($query, $request);

        return view('user.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => ProductCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'programmingLanguages' => ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get()
        ]);
    }

    private function applySearchFilter($query, ?ProductSearchRequest $request): void
    {
        if ($request?->filled('search')) {
            $search = htmlspecialchars(trim($request->validated('search')), ENT_QUOTES, 'UTF-8');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                });
            }
        }
    }

    private function applyCategoryFilter($query, ?ProductSearchRequest $request): void
    {
        if ($request?->filled('category') && is_numeric($request->validated('category'))) {
            $query->where('category_id', (int)$request->validated('category'));
        }
    }

    private function applyLanguageFilter($query, ?ProductSearchRequest $request): void
    {
        if ($request?->filled('language') && is_numeric($request->validated('language'))) {
            $query->where('programming_language', (int)$request->validated('language'));
        }
    }

    private function applyPriceFilter($query, ?ProductSearchRequest $request): void
    {
        if ($request?->filled('price_filter')) {
            $filter = $request->validated('price_filter');
            $query->where($filter === 'free' ? 'price' : 'price', $filter === 'free' ? 0 : '>', $filter === 'free' ? null : 0);
        }
    }

    private function applySorting($query, ?ProductSearchRequest $request): void
    {
        $sort = $request?->validated('sort', 'name') ?? 'name';
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc')
        };
    }

    public function show(Product $product): View
    {
        if (!$product->is_active) {
            abort(404);
        }
        return $this->showProduct($product);
    }

    public function publicShow(string $slug): View
    {
        $slug = htmlspecialchars(trim($slug), ENT_QUOTES, 'UTF-8');
        if (!$slug) {
            abort(404);
        }

        $product = Product::where('slug', $slug)->where('is_active', true)
            ->with(['category', 'programmingLanguage'])->firstOrFail();

        return $this->showProduct($product);
    }

    private function showProduct(Product $product): View
    {
        $user = Auth::user();
        $owns = $user ? $user->licenses()->where('product_id', $product->id)->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')->orWhere('license_expires_at', '>', now());
            })->exists() : false;

        $purchased = $user ? $user->licenses()->where('product_id', $product->id)->exists() : false;

        $download = $product->is_downloadable && $user ?
            app(ProductFileService::class)->userCanDownloadFiles($product, Auth::id() ?: 0) :
            ['can_download' => false, 'message' => ''];

        $product->description_has_html = is_string($product->description) && strip_tags($product->description) !== $product->description;
        $product->requirements_has_html = is_string($product->requirements) && strip_tags($product->requirements) !== $product->requirements;
        $product->installation_guide_has_html = is_string($product->installation_guide) && strip_tags($product->installation_guide) !== $product->installation_guide;

        return view('user.products.show', [
            'product' => $product,
            'userOwnsProduct' => $owns,
            'userCanDownload' => $download['can_download'],
            'downloadMessage' => $download['message'],
            'userHasPurchasedBefore' => $purchased,
            'licenseCount' => License::where('product_id', $product->id)->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('license_expires_at')->orWhere('license_expires_at', '>', now());
                })->count(),
            'relatedProducts' => Product::where('category_id', $product->category_id)->where('id', '!=', $product->id)
                ->where('is_active', true)->with(['category', 'programmingLanguage'])
                ->orderBy('created_at', 'desc')->limit(3)->get(),
            'screenshots' => $product->screenshots && !empty($product->screenshots) ?
                (is_string($product->screenshots) ? json_decode($product->screenshots, true) : $product->screenshots) : null
        ]);
    }
}
