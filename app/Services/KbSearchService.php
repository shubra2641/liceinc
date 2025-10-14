<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Support\Collection;

/**
 * Knowledge Base Search Service
 * 
 * Handles all search functionality for KB including query sanitization,
 * search execution, and result highlighting.
 */
class KbSearchService
{
    public function __construct(
        private KbAccessService $kbAccessService
    ) {
    }

    /**
     * Sanitize search query
     */
    public function sanitizeSearchQuery(string $query): string
    {
        $query = trim($query);
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        return strlen($query) > 255 ? substr($query, 0, 255) : $query;
    }

    /**
     * Get all categories with access
     */
    public function getAllCategoriesWithAccess(): Collection
    {
        $categories = KbCategory::where('is_active', true)
            ->with('product', 'articles')
            ->get();

        $user = auth()->user();
        $categories->each(function ($category) use ($user) {
            $category->hasAccess = $this->kbAccessService->checkCategoryAccess($category, $user);
        });

        return $categories;
    }

    /**
     * Perform search operation
     */
    public function performSearch(string $q): array
    {
        $searchTerm = '%' . strtolower($q) . '%';
        $user = auth()->user();

        $articles = $this->searchArticles($searchTerm);
        $categories = $this->searchCategories($searchTerm);

        $articles->each(fn($article) => $article->search_type = 'article');
        $categories->each(fn($category) => $category->search_type = 'category');

        $results = $articles->concat($categories);
        $resultsWithAccess = collect();
        $categoriesWithAccess = collect();

        foreach ($results as $item) {
            $hasAccess = $this->checkItemAccess($item, $user);
            $item->hasAccess = $hasAccess;

            if ($item instanceof KbArticle) {
                $resultsWithAccess->push($item);
            } else {
                $categoriesWithAccess->push($item);
                $resultsWithAccess->push($item);
            }
        }

        return [
            'results' => $results,
            'resultsWithAccess' => $resultsWithAccess,
            'categoriesWithAccess' => $categoriesWithAccess,
        ];
    }

    /**
     * Highlight search terms in text
     */
    public function highlightSearchTerm($text, $query): string
    {
        if (empty($query)) {
            return $text;
        }
        return preg_replace(
            '/(' . preg_quote($query, '/') . ')/i',
            '<mark class="search-highlight">$1</mark>',
            $text
        ) ?? $text;
    }

    /**
     * Search articles by query
     */
    private function searchArticles(string $searchTerm): Collection
    {
        return KbArticle::where('is_published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(title) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(content) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(excerpt) LIKE ?', [$searchTerm]);
            })
            ->with('category', 'product')
            ->get();
    }

    /**
     * Search categories by query
     */
    private function searchCategories(string $searchTerm): Collection
    {
        return KbCategory::where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm]);
            })
            ->with('product')
            ->get();
    }

    /**
     * Check access for search result item
     */
    private function checkItemAccess($item, $user): bool
    {
        if ($item instanceof KbArticle) {
            return $this->kbAccessService->checkArticleAccess($item, $user);
        } elseif ($item instanceof KbCategory) {
            return $this->kbAccessService->checkCategoryAccess($item, $user);
        }
        return true;
    }
}
