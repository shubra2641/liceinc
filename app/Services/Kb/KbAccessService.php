<?php

declare(strict_types=1);

namespace App\Services\Kb;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Product;
use App\Services\PurchaseCodeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Knowledge Base Access Service
 */
class KbAccessService
{
    public function __construct(
        private PurchaseCodeService $purchaseCodeService
    ) {
    }

    /**
     * Check if category requires access
     */
    public function categoryRequiresAccess(KbCategory $category): bool
    {
        return $category->requires_serial || $category->product_id;
    }

    /**
     * Check if article requires access
     */
    public function articleRequiresAccess(KbArticle $article): bool
    {
        return $article->requires_serial ||
               $article->requires_purchase_code ||
               $article->product_id ||
               $article->category->requires_serial ||
               $article->category->product_id;
    }

    /**
     * Check category access for user
     */
    public function checkCategoryAccess(KbCategory $category, $user): bool
    {
        if (!$this->categoryRequiresAccess($category)) {
            return true;
        }

        if (!$user || !$category->product_id) {
            return false;
        }

        return $user->licenses()
            ->where('product_id', $category->product_id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check article access for user
     */
    public function checkArticleAccess(KbArticle $article, $user): bool
    {
        if (!$this->articleRequiresAccess($article)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $productId = $article->product_id ?: $article->category->product_id;
        if (!$productId) {
            return false;
        }

        return $user->licenses()
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Handle raw code access for category
     */
    public function handleCategoryRawCodeAccess(KbCategory $category, string $rawCode): array
    {
        try {
            $result = $this->purchaseCodeService->verifyRawCode($rawCode, $category->product_id);

            if ($result['success']) {
                $license = $result['license'] ?? null;
                $productId = $result['product_id'] ?? ($license?->product_id);
                $product = $productId ? Product::find($productId) : null;

                if ($product && $product->id == $category->product_id) {
                    $accessToken = $this->generateAccessToken('category', $category->id, $license?->license_key);
                    $this->storeAccessToken($accessToken, [
                        'license_id' => $license?->id,
                        'product_id' => $product->id,
                        'category_id' => $category->id,
                        'expires_at' => now()->addHours(24),
                    ]);

                    return [
                        'success' => true,
                        'redirect' => redirect()->route('kb.category', [
                            'slug' => $category->slug,
                            'token' => $accessToken,
                        ]),
                    ];
                }
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Invalid license code',
            ];
        } catch (\Exception $e) {
            Log::error('Category raw code access failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Access verification failed'];
        }
    }

    /**
     * Handle raw code access for article
     */
    public function handleArticleRawCodeAccess(KbArticle $article, string $rawCode): array
    {
        try {
            $productId = $article->product_id ?: $article->category->product_id;
            $result = $this->purchaseCodeService->verifyRawCode($rawCode, $productId);

            if ($result['success']) {
                $license = $result['license'] ?? null;
                $productId = $result['product_id'] ?? ($license?->product_id);
                $product = $productId ? Product::find($productId) : null;

                if ($product && $product->id == $productId) {
                    $accessToken = $this->generateAccessToken('article', $article->id, $license?->license_key);
                    $this->storeAccessToken($accessToken, [
                        'license_id' => $license?->id,
                        'product_id' => $product->id,
                        'article_id' => $article->id,
                        'expires_at' => now()->addHours(24),
                    ]);

                    return [
                        'success' => true,
                        'redirect' => redirect()->route('kb.article', [
                            'slug' => $article->slug,
                            'token' => $accessToken,
                        ]),
                    ];
                }
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Invalid license code',
            ];
        } catch (\Exception $e) {
            Log::error('Article raw code access failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Access verification failed'];
        }
    }

    /**
     * Validate access token
     */
    public function validateAccessToken(string $accessToken, int $categoryId): array
    {
        if (Session::has($accessToken)) {
            $tokenData = Session::get($accessToken);
            if (
                is_array($tokenData) &&
                isset($tokenData['expires_at']) &&
                isset($tokenData['category_id']) &&
                $tokenData['expires_at'] > now() &&
                $tokenData['category_id'] == $categoryId
            ) {
                return ['valid' => true];
            }
            Session::forget($accessToken);
        }
        return ['valid' => false];
    }

    /**
     * Validate article access token
     */
    public function validateArticleAccessToken(string $accessToken, int $articleId): array
    {
        if (Session::has($accessToken)) {
            $tokenData = Session::get($accessToken);
            if (
                is_array($tokenData) &&
                isset($tokenData['expires_at']) &&
                isset($tokenData['article_id']) &&
                $tokenData['expires_at'] > now() &&
                $tokenData['article_id'] == $articleId
            ) {
                return ['valid' => true];
            }
            Session::forget($accessToken);
        }
        return ['valid' => false];
    }

    /**
     * Generate access token
     */
    private function generateAccessToken(string $type, int $id, ?string $licenseKey): string
    {
        return "kb_{$type}_access_{$id}_" . time() . "_" . substr(md5($licenseKey ?? ''), 0, 8);
    }

    /**
     * Store access token in session
     */
    private function storeAccessToken(string $token, array $data): void
    {
        Session::put($token, $data);
    }
}
