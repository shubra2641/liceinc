<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kb;

use App\Http\Controllers\Controller;
use App\Services\Kb\KbAccessService;
use App\Services\Kb\KbDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Knowledge Base Category Controller
 */
class KbCategoryController extends Controller
{
    public function __construct(
        private KbDataService $dataService,
        private KbAccessService $accessService
    ) {
    }

    /**
     * Display KB category
     */
    public function show(string $slug): View|RedirectResponse
    {
        try {
            $this->validateSlug($slug);
            $category = $this->dataService->getCategoryBySlug($slug);

            if (!$this->accessService->categoryRequiresAccess($category)) {
                return $this->showPublicCategory($category);
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this category.');
            }

            $user = auth()->user();
            $hasAccess = $this->accessService->checkCategoryAccess($category, $user);
            $accessSource = 'user_license';

            if (!$hasAccess && request()->query('raw_code')) {
                $result = $this->accessService->handleCategoryRawCodeAccess(
                    $category,
                    request()->query('raw_code')
                );
                if ($result['success']) {
                    return $result['redirect'];
                }
                return redirect()->route('kb.category', ['slug' => $category->slug])
                    ->with('error', $result['error']);
            }

            if (!$hasAccess && request()->query('token')) {
                $tokenResult = $this->accessService->validateAccessToken(
                    request()->query('token'),
                    $category->id
                );
                if ($tokenResult['valid']) {
                    $hasAccess = true;
                    $accessSource = 'token';
                }
            }

            if ($hasAccess) {
                return $this->showProtectedCategory($category, $accessSource);
            }

            return view('kb.category-purchase', compact('category'));
        } catch (\Exception $e) {
            Log::error('KB category failed', ['error' => $e->getMessage(), 'slug' => $slug]);
            return redirect()->route('kb.index')->with('error', 'Category not found.');
        }
    }

    /**
     * Show public category
     */
    private function showPublicCategory($category): View
    {
        $articles = $this->dataService->getCategoryArticles($category);
        $relatedCategories = $this->dataService->getRelatedCategories($category);
        
        return view('kb.category', compact('category', 'articles', 'relatedCategories'));
    }

    /**
     * Show protected category
     */
    private function showProtectedCategory($category, string $accessSource): View
    {
        $articles = $this->dataService->getCategoryArticles($category);
        $relatedCategories = $this->dataService->getRelatedCategories($category);
        
        return view('kb.category', compact('category', 'articles', 'relatedCategories', 'accessSource'));
    }

    /**
     * Validate slug
     */
    private function validateSlug(string $slug): void
    {
        if (empty($slug) || strlen($slug) > 255) {
            throw new \InvalidArgumentException('Invalid slug');
        }
    }
}
