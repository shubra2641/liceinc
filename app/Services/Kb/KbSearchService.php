<?php

declare(strict_types=1);

namespace App\Services\Kb;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Knowledge Base Search Service
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
     * Perform search
     */
    public function performSearch(string $query, $user = null): array
    {
        $searchTerm = '%' . strtolower($query) . '%';

        $articles = $this->searchArticles($searchTerm);
        $categories = $this->searchCategories($searchTerm);

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
     * Search articles
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
     * Search categories
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
     * Highlight search terms in text
     */
    public function highlightSearchTerm(string $text, string $query): string
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
