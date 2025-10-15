<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Kb\KbIndexController;
use App\Http\Controllers\Kb\KbCategoryController;
use App\Http\Controllers\Kb\KbArticleController;
use App\Http\Controllers\Kb\KbSearchController;

/**
 * Knowledge Base Public Controller - Refactored
 * 
 * This controller now delegates to specialized controllers
 * for better organization and maintainability.
 */
class KbPublicController extends Controller
{
    public function __construct(
        private KbIndexController $indexController,
        private KbCategoryController $categoryController,
        private KbArticleController $articleController,
        private KbSearchController $searchController
    ) {
    }

    /**
     * Display KB index
     */
    public function index()
    {
        return $this->indexController->index();
    }

    /**
     * Display KB category
     */
    public function category(string $slug)
    {
        return $this->categoryController->show($slug);
    }

    /**
     * Display KB article
     */
    public function article(string $slug)
    {
        return $this->articleController->show($slug);
    }

    /**
     * Search KB
     */
    public function search(\Illuminate\Http\Request $request)
    {
        return $this->searchController->search($request);
    }

    /**
     * Highlight search terms - Static helper method
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
