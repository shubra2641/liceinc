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
 * Knowledge Base Article Controller
 */
class KbArticleController extends Controller
{
    public function __construct(
        private KbDataService $dataService,
        private KbAccessService $accessService
    ) {
    }

    /**
     * Display KB article
     */
    public function show(string $slug): View|RedirectResponse
    {
        try {
            $this->validateSlug($slug);
            $article = $this->dataService->getArticleBySlug($slug);

            if (!$this->accessService->articleRequiresAccess($article)) {
                return $this->showPublicArticle($article);
            }

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access this article.');
            }

            $user = auth()->user();
            $hasAccess = $this->accessService->checkArticleAccess($article, $user);
            $accessSource = 'user_license';

            if (!$hasAccess && request()->query('raw_code')) {
                $result = $this->accessService->handleArticleRawCodeAccess(
                    $article,
                    request()->query('raw_code')
                );
                if ($result['success']) {
                    return $result['redirect'];
                }
                return redirect()->route('kb.article', ['slug' => $article->slug])
                    ->with('error', $result['error']);
            }

            if (!$hasAccess && request()->query('token')) {
                $tokenResult = $this->accessService->validateArticleAccessToken(
                    request()->query('token'),
                    $article->id
                );
                if ($tokenResult['valid']) {
                    $hasAccess = true;
                    $accessSource = 'token';
                }
            }

            if ($hasAccess) {
                return $this->showProtectedArticle($article, $accessSource);
            }

            return view('kb.article-purchase', compact('article'));
        } catch (\Exception $e) {
            Log::error('KB article failed', ['error' => $e->getMessage(), 'slug' => $slug]);
            return redirect()->route('kb.index')->with('error', 'Article not found.');
        }
    }

    /**
     * Show public article
     */
    private function showPublicArticle($article): View
    {
        $this->dataService->incrementArticleViews($article);
        $related = $this->dataService->getRelatedArticles($article);
        
        return view('kb.article', compact('article', 'related'));
    }

    /**
     * Show protected article
     */
    private function showProtectedArticle($article, string $accessSource): View
    {
        $this->dataService->incrementArticleViews($article);
        $related = $this->dataService->getRelatedArticles($article);
        
        return view('kb.article', compact('article', 'related', 'accessSource'));
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
