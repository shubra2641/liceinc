<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kb;

use App\Http\Controllers\Controller;
use App\Services\Kb\KbDataService;
use App\Services\Kb\KbSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Knowledge Base Search Controller
 */
class KbSearchController extends Controller
{
    public function __construct(
        private KbDataService $dataService,
        private KbSearchService $searchService
    ) {
    }

    /**
     * Search KB
     */
    public function search(Request $request): View
    {
        try {
            $request->validate([
                'q' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
                'page' => 'sometimes|integer|min:1',
            ]);

            $q = $this->searchService->sanitizeSearchQuery($request->get('q', ''));
            $results = collect();
            $resultsWithAccess = collect();
            $categoriesWithAccess = collect();

            $allCategories = $this->dataService->getAllCategoriesWithAccess(auth()->user());

            if ($q !== '') {
                $searchResults = $this->searchService->performSearch($q, auth()->user());
                $results = $searchResults['results'];
                $resultsWithAccess = $searchResults['resultsWithAccess'];
                $categoriesWithAccess = $searchResults['categoriesWithAccess'];
            } else {
                $categoriesWithAccess = $allCategories;
            }

            $highlightQuery = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');
            
            return view('kb.search', compact(
                'q',
                'results',
                'resultsWithAccess',
                'categoriesWithAccess',
                'highlightQuery'
            ));
        } catch (\Exception $e) {
            Log::error('KB search failed', ['error' => $e->getMessage()]);
            return view('kb.search', [
                'q' => '',
                'results' => collect(),
                'resultsWithAccess' => collect(),
                'categoriesWithAccess' => collect(),
                'highlightQuery' => '',
            ]);
        }
    }
}
