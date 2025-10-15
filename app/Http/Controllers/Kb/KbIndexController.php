<?php

declare(strict_types=1);

namespace App\Http\Controllers\Kb;

use App\Http\Controllers\Controller;
use App\Services\Kb\KbDataService;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Knowledge Base Index Controller
 */
class KbIndexController extends Controller
{
    public function __construct(
        private KbDataService $dataService
    ) {
    }

    /**
     * Display KB index
     */
    public function index(): View
    {
        try {
            $categories = $this->dataService->getActiveCategories();
            $latest = $this->dataService->getLatestArticles();

            return view('kb.index', compact('categories', 'latest'));
        } catch (\Exception $e) {
            Log::error('KB index failed', ['error' => $e->getMessage()]);
            return view('kb.index', [
                'categories' => collect(),
                'latest' => collect()
            ]);
        }
    }
}
