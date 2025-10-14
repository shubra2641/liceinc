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

        if ($request?->filled('search')) {
            $search = $this->sanitize($request->validated('search'));
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                });
            }
        }

        if ($request?->filled('category') && is_numeric($request->validated('category'))) {
            $query->where('category_id', (int)$request->validated('category'));
        }

        if ($request?->filled('language') && is_numeric($request->validated('language'))) {
            $query->where('programming_language', (int)$request->validated('language'));
        }

        if ($request?->filled('price_filter')) {
            $filter = $request->validated('price_filter');
            $query->where($filter === 'free' ? 'price' : 'price', $filter === 'free' ? 0 : '>', $filter === 'free' ? null : 0);
        }

        $sort = $request?->validated('sort', 'name') ?? 'name';
        $this->sort($query, $sort);

        return view('user.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => ProductCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'programmingLanguages' => ProgrammingLanguage::where('is_active', true)->orderBy('sort_order')->get()
        ]);
    }

    public function show(Product $product): View
    {
        if (!$product->is_active) abort(404);
        return $this->showProduct($product);
    }

    public function publicShow(string $slug): View
    {
        $slug = $this->sanitize($slug);
        if (!$slug) abort(404);
        
        $product = Product::where('slug', $slug)->where('is_active', true)
            ->with(['category', 'programmingLanguage'])->firstOrFail();
        
        return $this->showProduct($product);
    }

    private function showProduct(Product $product): View
    {
        $data = $this->getData($product);
        return view('user.products.show', array_merge(['product' => $product], $data));
    }

    private function sort($query, string $sort): void
    {
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc')
        };
    }

    private function getData(Product $product): array
    {
        $user = Auth::user();
        $owns = $user ? $this->owns($user, $product) : false;
        $purchased = $user ? $this->purchased($user, $product) : false;
        
        $download = $product->is_downloadable && $user ? $this->download($product) : ['can_download' => false, 'message' => ''];
        
        $this->process($product);
        
        return [
            'userOwnsProduct' => $owns,
            'userCanDownload' => $download['can_download'],
            'downloadMessage' => $download['message'],
            'userHasPurchasedBefore' => $purchased,
            'licenseCount' => $this->licenseCount($product),
            'relatedProducts' => $this->related($product),
            'screenshots' => $this->screenshots($product)
        ];
    }

    private function owns($user, Product $product): bool
    {
        return $user->licenses()->where('product_id', $product->id)->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')->orWhere('license_expires_at', '>', now());
            })->exists();
    }

    private function purchased($user, Product $product): bool
    {
        return $user->licenses()->where('product_id', $product->id)->exists();
    }

    private function download(Product $product): array
    {
        return app(ProductFileService::class)->userCanDownloadFiles($product, Auth::id() ?: 0);
    }

    private function process(Product $product): void
    {
        $product->description_has_html = $this->hasHtml($product->description);
        $product->requirements_has_html = $this->hasHtml($product->requirements);
        $product->installation_guide_has_html = $this->hasHtml($product->installation_guide);
    }

    private function hasHtml($content): bool
    {
        return is_string($content) && strip_tags($content) !== $content;
    }

    private function licenseCount(Product $product): int
    {
        return License::where('product_id', $product->id)->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')->orWhere('license_expires_at', '>', now());
            })->count();
    }

    private function related(Product $product)
    {
        return Product::where('category_id', $product->category_id)->where('id', '!=', $product->id)
            ->where('is_active', true)->with(['category', 'programmingLanguage'])
            ->orderBy('created_at', 'desc')->limit(3)->get();
    }

    private function screenshots(Product $product)
    {
        if (!$product->screenshots || empty($product->screenshots)) return null;
        return is_string($product->screenshots) ? json_decode($product->screenshots, true) : $product->screenshots;
    }

    private function sanitize(?string $input): string
    {
        return $input ? htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8') : '';
    }
}