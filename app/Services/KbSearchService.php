<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Support\Collection;

/**
 * Knowledge Base Search Service
 * 
 * Handles search functionality for KB articles and categories
 */
class KbSearchService
{
    public function __construct(
        private KbAccessService $accessService
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
            $category->hasAccess = $this->accessService->checkCategoryAccess($category, $user);
        });

        return $categories;
    }

    /**
     * Perform search
     */
    public function performSearch(string $q): array
    {
        $searchTerm = '%' . strtolower($q) . '%';
        $user = auth()->user();

        $articles = KbArticle::where('is_published', true)
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

        $categories = KbCategory::where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm]);
            })
            ->with('product')
            ->get();

        $articles->each(fn($article) => $article->search_type = 'article');
        $categories->each(fn($category) => $category->search_type = 'category');

        $results = $articles->concat($categories);
        $resultsWithAccess = collect();
        $categoriesWithAccess = collect();

        foreach ($results as $item) {
            $hasAccess = true;
            if ($item instanceof KbArticle) {
                $hasAccess = $this->accessService->checkArticleAccess($item, $user);
            } elseif ($item instanceof KbCategory) {
                $hasAccess = $this->accessService->checkCategoryAccess($item, $user);
            }

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
     * Highlight search terms
     */
    public static function highlightSearchTerm($text, $query)
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
}
