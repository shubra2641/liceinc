<?php

declare(strict_types=1);

namespace App\Services\Kb;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Knowledge Base Data Service
 */
class KbDataService
{
    /**
     * Get all active categories with counts
     */
    public function getActiveCategories(): Collection
    {
        return KbCategory::where('is_active', true)
            ->withCount('children')
            ->with('product')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get latest published articles
     */
    public function getLatestArticles(int $limit = 6): Collection
    {
        return KbArticle::where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get category by slug
     */
    public function getCategoryBySlug(string $slug): KbCategory
    {
        return KbCategory::where('slug', $slug)
            ->with('product')
            ->firstOrFail();
    }

    /**
     * Get article by slug
     */
    public function getArticleBySlug(string $slug): KbArticle
    {
        return KbArticle::where('slug', $slug)
            ->where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->firstOrFail();
    }

    /**
     * Get category articles with pagination
     */
    public function getCategoryArticles(KbCategory $category, int $perPage = 10)
    {
        return KbArticle::where('kb_category_id', $category->id)
            ->where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get related categories
     */
    public function getRelatedCategories(KbCategory $category, int $limit = 4): Collection
    {
        return KbCategory::where('id', '!=', $category->id)
            ->where('is_active', true)
            ->with('product')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Get related articles
     */
    public function getRelatedArticles(KbArticle $article, int $limit = 3): Collection
    {
        return KbArticle::where('kb_category_id', $article->kb_category_id)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with('category', 'product')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Increment article views
     */
    public function incrementArticleViews(KbArticle $article): void
    {
        $article->increment('views');
    }

    /**
     * Get all categories with access info
     */
    public function getAllCategoriesWithAccess($user = null): Collection
    {
        $categories = KbCategory::where('is_active', true)
            ->with('product', 'articles')
            ->get();

        if ($user) {
            $accessService = app(KbAccessService::class);
            $categories->each(function ($category) use ($user, $accessService) {
                $category->hasAccess = $accessService->checkCategoryAccess($category, $user);
            });
        }

        return $categories;
    }
}
