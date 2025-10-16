<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VerifyArticleSerialRequest;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Knowledge Base API Controller.
 *
 * This controller handles API endpoints for knowledge base articles and categories.
 * It provides serial verification for protected content and manages article access.
 *
 * Features:
 * - Serial verification for protected articles and categories
 * - Article and category requirements checking
 * - View tracking for articles
 * - Comprehensive error handling with database transactions
 * - Clean API responses with proper status codes
 * - Support for both article-level and category-level serial protection
 * - Enhanced security measures (XSS protection, input validation)
 * - Rate limiting and CSRF protection
 * - Proper logging for errors and warnings only
 *
 * @example
 * // Verify serial for article access
 * POST /api/kb/article/{slug}/verify
 * {
 *     "serial": "ABC123-DEF456"
 * }
 */
class KbApiController extends Controller
{
    /**
     * Verify serial for article access.
     *
     * Validates serial codes for accessing protected knowledge base articles.
     * Supports both article-level and category-level serial protection.
     *
     * @param  VerifyArticleSerialRequest  $request  The validated request containing serial code
     * @param  string  $articleSlug  The article slug to verify access for
     *
     * @return JsonResponse Response with article content or error message
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Verify serial for article access:
     * POST /api/kb/article/{slug}/verify
     * {
     *     "serial": "ABC123-DEF456"
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "Serial verified successfully",
     *     "data": {
     *         "content": "Article content...",
     *         "title": "Article Title",
     *         "serial_source": "article"
     *     }
     * }
     */
    public function verifyArticleSerial(VerifyArticleSerialRequest $request, string $articleSlug): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Sanitize input to prevent XSS
            $articleSlug = $this->sanitizeInput($articleSlug);
            $articleSlugStr = is_string($articleSlug) ? $articleSlug : '';
            $article = $this->findArticleBySlug($articleSlugStr);
            if (! $article) {
                DB::rollBack();

                return $this->errorResponse('Article not found', 404);
            }
            if (! $this->requiresSerialVerification($article)) {
                DB::commit();

                return $this->successResponse([
                    'content' => $this->sanitizeOutput($article->content),
                    'title' => $this->sanitizeOutput($article->title),
                ]);
            }
            $serial = is_string($validated['serial']) ? $validated['serial'] : '';
            $serialValidation = $this->validateSerial($article, $serial);
            if (! $serialValidation['valid']) {
                DB::rollBack();
                Log::warning('Invalid serial attempt for article', [
                    'article_slug' => $articleSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->errorResponse('Invalid serial code', 403);
            }
            $this->incrementArticleViews($article);
            DB::commit();

            return $this->successResponse([
                'content' => $this->sanitizeOutput($article->content),
                'title' => $this->sanitizeOutput($article->title),
                'serial_source' => $serialValidation['source'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying article serial', [
                'article_slug' => $articleSlug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('An error occurred while verifying serial', 500);
        }
    }

    /**
     * Get article serial requirements.
     *
     * Retrieves information about whether an article requires serial verification
     * and provides appropriate messages for the user interface.
     *
     * @param  string  $articleSlug  The article slug to check requirements for
     *
     * @return JsonResponse Response with serial requirements information
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Get article requirements:
     * GET /api/kb/article/{slug}/requirements
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "Article requirements retrieved",
     *     "data": {
     *         "requires_serial": true,
     *         "title": "Article Title",
     *         "excerpt": "Article excerpt...",
     *         "serial_message": "Please enter the serial code to access this article.",
     *         "serial_source": "article"
     *     }
     * }
     */
    public function getArticleRequirements(string $articleSlug): JsonResponse
    {
        try {
            // Sanitize input to prevent XSS
            $articleSlug = $this->sanitizeInput($articleSlug);
            $articleSlugStr = is_string($articleSlug) ? $articleSlug : '';
            $article = $this->findArticleWithCategory($articleSlugStr);
            if (! $article) {
                return $this->errorResponse('Article not found', 404);
            }
            $requiresSerial = $this->requiresSerialVerification($article);
            $responseData = [
                'requires_serial' => $requiresSerial,
                'title' => $this->sanitizeOutput($article->title),
                'excerpt' => $this->sanitizeOutput($article->excerpt),
            ];
            if ($requiresSerial) {
                $serialInfo = $this->getSerialInfo($article);
                $message = is_string($serialInfo['message'] ?? null) ? $serialInfo['message'] : null;
                $responseData['serial_message'] = $this->sanitizeOutput($message);
                $responseData['serial_source'] = $serialInfo['source'];
            }

            return $this->successResponse($responseData, 'Article requirements retrieved');
        } catch (\Exception $e) {
            Log::error('Error getting article requirements', [
                'article_slug' => $articleSlug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('An error occurred while retrieving article requirements', 500);
        }
    }

    /**
     * Get category serial requirements.
     *
     * Retrieves information about whether a category requires serial verification
     * and provides appropriate messages for the user interface.
     *
     * @param  string  $categorySlug  The category slug to check requirements for
     *
     * @return JsonResponse Response with category serial requirements information
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Get category requirements:
     * GET /api/kb/category/{slug}/requirements
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "Category requirements retrieved",
     *     "data": {
     *         "requires_serial": true,
     *         "name": "Category Name",
     *         "description": "Category description...",
     *         "articles_count": 5,
     *         "serial_message": "Please enter the serial code to access articles in this category."
     *     }
     * }
     */
    public function getCategoryRequirements(string $categorySlug): JsonResponse
    {
        try {
            // Sanitize input to prevent XSS
            $categorySlug = $this->sanitizeInput($categorySlug);
            $categorySlugStr = is_string($categorySlug) ? $categorySlug : '';
            $category = $this->findCategoryWithArticles($categorySlugStr);
            if (! $category) {
                return $this->errorResponse('Category not found', 404);
            }
            $responseData = [
                'requires_serial' => $category->requires_serial,
                'name' => $this->sanitizeOutput($category->name),
                'description' => $this->sanitizeOutput($category->description),
                'articles_count' => $category->articles->count(),
            ];
            if ($category->requires_serial) {
                $serialMessage = $category->serial_message
                    ?: 'Please enter the serial code to access articles in this category.';
                $responseData['serial_message'] = $this->sanitizeOutput($serialMessage);
            }

            return $this->successResponse($responseData, 'Category requirements retrieved');
        } catch (\Exception $e) {
            Log::error('Error getting category requirements', [
                'category_slug' => $categorySlug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('An error occurred while retrieving category requirements', 500);
        }
    }

    /**
     * Find article by slug with published scope.
     *
     * @param  string  $slug  The article slug
     *
     * @return KbArticle|null The article or null if not found
     */
    private function findArticleBySlug(string $slug): ?KbArticle
    {
        return KbArticle::published()->where('slug', $slug)->first();
    }

    /**
     * Find article with category relationship using published scope.
     *
     * @param  string  $slug  The article slug
     *
     * @return KbArticle|null The article with category or null if not found
     */
    private function findArticleWithCategory(string $slug): ?KbArticle
    {
        return KbArticle::published()->with('category')->where('slug', $slug)->first();
    }

    /**
     * Find category with articles relationship using active scope.
     *
     * @param  string  $slug  The category slug
     *
     * @return KbCategory|null The category with articles or null if not found
     */
    private function findCategoryWithArticles(string $slug): ?KbCategory
    {
        return KbCategory::active()->with(['articles' => function ($query) {
            if (is_object($query) && method_exists($query, 'published')) {
                $query->published();
            }
        }])->where('slug', $slug)->first();
    }

    /**
     * Check if article requires serial verification.
     *
     * @param  KbArticle  $article  The article to check
     *
     * @return bool True if serial verification is required
     */
    private function requiresSerialVerification(KbArticle $article): bool
    {
        return $article->requires_serial ||
               $article->category->requires_serial;
    }

    /**
     * Validate serial code.
     *
     * @param  KbArticle  $article  The article to validate against
     * @param  string  $serial  The serial code to validate
     *
     * @return array<string, mixed> Validation result with 'valid' and 'source' keys
     */
    private function validateSerial(KbArticle $article, string $serial): array
    {
        if ($article->requires_serial && $article->serial === $serial) {
            return ['valid' => true, 'source' => 'article'];
        }
        if (
            $article->category->requires_serial &&
            $article->category->serial === $serial
        ) {
            return ['valid' => true, 'source' => 'category'];
        }

        return ['valid' => false, 'source' => ''];
    }

    /**
     * Get serial information for article.
     *
     * @param  KbArticle  $article  The article to get serial info for
     *
     * @return array Serial information with 'message' and 'source' keys
     */
    /**
     * @return array<string, mixed>
     */
    private function getSerialInfo(KbArticle $article): array
    {
        if ($article->requires_serial) {
            return [
                'message' => $article->serial_message ?: 'Please enter the serial code to access this article.',
                'source' => 'article',
            ];
        }
        if ($article->category->requires_serial) {
            return [
                'message' => $article->category->serial_message
                    ?: 'Please enter the serial code to access articles in this category.',
                'source' => 'category',
            ];
        }

        return ['message' => '', 'source' => ''];
    }

    /**
     * Increment article views with error handling.
     *
     * @param  KbArticle  $article  The article to increment views for
     *
     * @throws \Exception When view increment fails
     */
    private function incrementArticleViews(KbArticle $article): void
    {
        try {
            $article->increment('views');
        } catch (\Exception $e) {
            Log::error('Failed to increment article views', [
                'article_id' => $article->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create success response with proper headers.
     *
     * @param  string  $message  The success message
     * @param  array  $data  The response data
     *
     * @return JsonResponse The success response
     */
    /**
     * @param array<string, mixed>|null $data
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        string $dataKey = 'data',
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
        $response[$dataKey] = $data;

        return response()->json($response, $statusCode);
    }

    /**
     * Create error response with proper headers.
     *
     * @param  string  $message  The error message
     * @param  int  $statusCode  The HTTP status code
     *
     * @return JsonResponse The error response
     */
    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = 400,
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     */
    protected function sanitizeInput(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        if (is_object($input)) {
            return $input;
        }
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }

        return $input;
    }

    /**
     * Sanitize output to prevent XSS attacks.
     *
     * @param  string|null  $output  The output to sanitize
     *
     * @return string The sanitized output
     */
    private function sanitizeOutput(?string $output): string
    {
        if ($output === null) {
            return '';
        }

        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}
