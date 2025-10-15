<?php

namespace App\Services;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\JsonResponse;

/**
 * Service for handling product KB operations
 */
class ProductKbService
{
    /**
     * Get KB categories and articles
     */
    public function getKbData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'categories' => KbCategory::where('is_published', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'articles' => KbArticle::where('is_published', true)
                ->with('category:id, name')
                ->orderBy('title')
                ->get(['id', 'title', 'slug', 'kb_category_id']),
        ]);
    }

    /**
     * Get KB articles for specific category
     */
    public function getKbArticles(int $categoryId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'articles' => KbArticle::where('kb_category_id', $categoryId)
                ->where('is_published', true)
                ->with('category:id, name')
                ->orderBy('title')
                ->get(['id', 'title', 'slug', 'kb_category_id']),
        ]);
    }
}
