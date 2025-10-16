<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $envato_item_id
 * @property string|null $purchase_url_envato
 * @property string|null $purchase_url_buy
 * @property string|null $description Description as string
 * @property float $price
 * @property string $status
 * @property string $license_type
 * @property int $support_days
 * @property \Illuminate\Support\Carbon|null $supported_until
 * @property float|null $extended_support_price
 * @property int|null $extended_support_days
 * @property \Illuminate\Support\Carbon|null $extended_supported_until
 * @property array<array-key, mixed>|null $kb_categories Array of KB category IDs linked to this product
 * @property array<array-key, mixed>|null $kb_articles Array of KB article IDs linked to this product
 * @property bool $kb_access_required Whether KB access is required for this product
 * @property string|null $kb_access_message Custom message for KB access requirement
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $category_id
 * @property int|null $kb_category_id
 * @property int|null $programming_language
 * @property string|null $integration_file_path
 * @property float|null $renewal_price
 * @property string|null $renewal_period
 * @property float $tax_rate
 * @property int $stock_quantity
 * @property bool $requires_domain
 * @property array<array-key, mixed>|null $features
 * @property string|null $requirements Requirements as string (stored as text in DB)
 * @property string|null $installation_guide Installation guide as string (stored as text in DB)
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array<array-key, mixed>|null $tags
 * @property string|null $version
 * @property bool $is_featured
 * @property bool $is_popular
 * @property bool $is_downloadable
 * @property string|null $image
 * @property array<array-key, mixed>|null $gallery_images
 * @property int|null $stock
 * @property int $duration_days
 * @property bool $auto_renewal
 * @property int $renewal_reminder_days
 * @property-read Collection<int, ProductFile> $activeFiles
 * @property-read int|null $active_files_count
 * @property-read ProductCategory|null $category
 * @property-read Collection<int, ProductFile> $files
 * @property-read int|null $files_count
 * @property-read string $current_version
 * @property-read mixed $formatted_extended_support_price
 * @property-read mixed $formatted_price
 * @property-read mixed $formatted_renewal_price
 * @property-read string $latest_version
 * @property-read mixed $renewal_period_label
 * @property-read mixed $stock_status
 * @property-read mixed $tax_amount
 * @property-read mixed $total_price
 * @property-read Collection<int, Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read Collection<int, KbArticle> $kbArticles
 * @property-read int|null $kb_articles_count
 * @property-read Collection<int, KbCategory> $kbCategories
 * @property-read int|null $kb_categories_count
 * @property-read ProductUpdate|null $latestUpdate
 * @property-read Collection<int, License> $licenses
 * @property-read int|null $licenses_count
 * @property-read ProgrammingLanguage|null $programmingLanguage
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAutoRenewal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDurationDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEnvatoItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereExtendedSupportDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereExtendedSupportPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereExtendedSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereInstallationGuide($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIntegrationFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsDownloadable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsPopular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKbAccessMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKbAccessRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKbArticles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKbCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKbCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProgrammingLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePurchaseUrlBuy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePurchaseUrlEnvato($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRenewalPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRenewalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRenewalReminderDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRequiresDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStockQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSupportDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVersion($value)
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    protected $fillable = [
        'name',
        'slug',
        'purchase_url_envato',
        'purchase_url_buy',
        'envato_item_id',
        'description',
        'price',
        'status',
        'license_type',
        'renewal_price',
        'renewal_period',
        'support_days',
        'tax_rate',
        'stock_quantity',
        'supported_until',
        'extended_support_price',
        'extended_support_days',
        'extended_supported_until',
        'is_active',
        'integration_file_path',
        'programming_language',
        'category_id',
        'kb_category_id',
        'requires_domain',
        'features',
        'requirements',
        'installation_guide',
        'meta_title',
        'meta_description',
        'tags',
        'is_featured',
        'is_popular',
        'is_downloadable',
        'image',
        'gallery_images',
        'version',
        'kb_categories',
        'kb_articles',
        'kb_access_required',
        'kb_access_message',
        'stock',
        'duration_days',
        'auto_renewal',
        'renewal_reminder_days',
    ];

    protected $casts = [
        'price' => 'float',
        'extended_support_price' => 'float',
        'renewal_price' => 'float',
        'tax_rate' => 'float',
        'supported_until' => 'datetime',
        'extended_supported_until' => 'datetime',
        'extended_support_days' => 'integer',
        'support_days' => 'integer',
        'duration_days' => 'integer',
        'renewal_reminder_days' => 'integer',
        'stock_quantity' => 'integer',
        'stock' => 'integer',
        'category_id' => 'integer',
        'programming_language' => 'integer',
        'kb_category_id' => 'integer',
        'envato_item_id' => 'integer',
        'version' => 'string',
        'image' => 'string',
        'meta_title' => 'string',
        'meta_description' => 'string',
        'slug' => 'string',
        'name' => 'string',
        'description' => 'string',
        'purchase_url_envato' => 'string',
        'purchase_url_buy' => 'string',
        'integration_file_path' => 'string',
        'status' => 'string',
        'license_type' => 'string',
        'renewal_period' => 'string',
        'is_active' => 'boolean',
        'requires_domain' => 'boolean',
        'is_featured' => 'boolean',
        'is_popular' => 'boolean',
        'is_downloadable' => 'boolean',
        'kb_access_required' => 'boolean',
        'features' => 'array',
        'requirements' => 'array',
        'installation_guide' => 'array',
        'tags' => 'array',
        'gallery_images' => 'array',
        'kb_categories' => 'array',
        'kb_articles' => 'array',
        'auto_renewal' => 'boolean',
    ];

    /**
     * @return HasMany<License, $this>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    /**
     * Get product files.
     */
    /**
     * @return HasMany<ProductFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class);
    }

    /**
     * Get the product updates.
     */
    /**
     * @return HasMany<ProductUpdate, $this>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(ProductUpdate::class);
    }

    /**
     * Get the latest update for this product.
     */
    /**
     * @return HasOne<ProductUpdate, $this>
     */
    public function latestUpdate(): HasOne
    {
        return $this->hasOne(ProductUpdate::class)->active()->latest('version');
    }

    /**
     * Get the current version of the product.
     */
    public function getCurrentVersionAttribute(): string
    {
        return $this->latestUpdate->version ?? $this->version ?? '1.0.0';
    }

    /**
     * Get active product files.
     */
    /**
     * @return HasMany<ProductFile, $this>
     */
    public function activeFiles(): HasMany
    {
        return $this->hasMany(ProductFile::class)->where('is_active', true);
    }

    /**
     * @return BelongsTo<ProgrammingLanguage, $this>
     */
    public function programmingLanguage(): BelongsTo
    {
        return $this->belongsTo(ProgrammingLanguage::class, 'programming_language');
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        // Specify foreign key explicitly to avoid default 'product_category_id'
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get KB categories linked to this product.
     */
    /**
     * @return BelongsToMany<KbCategory, $this>
     */
    public function kbCategories(): BelongsToMany
    {
        return $this->belongsToMany(KbCategory::class, 'product_kb_categories', 'product_id', 'kb_category_id');
    }

    /**
     * Get KB articles linked to this product.
     */
    /**
     * @return BelongsToMany<KbArticle, $this>
     */
    public function kbArticles(): BelongsToMany
    {
        return $this->belongsToMany(KbArticle::class, 'product_kb_articles', 'product_id', 'kb_article_id');
    }

    /**
     * Get KB categories by IDs stored in JSON.
     */
    /**
     * @return array<int>
     */
    public function getKbCategoriesAttribute(?string $value = null): array
    {
        if (! $value) {
            return [];
        }
        $categoryIds = json_decode($value, true) ?? [];

        return is_array($categoryIds) ? array_map(fn ($id) => is_numeric($id) ? (int)$id : 0, $categoryIds) : [];
    }

    /**
     * Get KB articles by IDs stored in JSON.
     */
    /**
     * @return array<int>
     */
    public function getKbArticlesAttribute(?string $value = null): array
    {
        if (! $value) {
            return [];
        }
        $articleIds = json_decode($value, true) ?? [];

        return is_array($articleIds) ? array_map(fn ($id) => is_numeric($id) ? (int)$id : 0, $articleIds) : [];
    }

    /**
     * Get KB categories as Collection.
     */
    /**
     * @return Collection<int, KbCategory>
     */
    public function getKbCategoriesCollection(): Collection
    {
        if (! $this->kb_categories) {
            return new Collection();
        }

        return KbCategory::whereIn('id', $this->kb_categories)->get();
    }

    /**
     * Get KB articles as Collection.
     */
    /**
     * @return Collection<int, KbArticle>
     */
    public function getKbArticlesCollection(): Collection
    {
        if (! $this->kb_articles) {
            return new Collection();
        }

        return KbArticle::whereIn('id', $this->kb_articles)->get();
    }

    /**
     * Check if product has KB access requirements.
     */
    public function hasKbAccess(): bool
    {
        return $this->kb_access_required &&
               ((is_array($this->kb_categories) && count($this->kb_categories) > 0) ||
                (is_array($this->kb_articles) && count($this->kb_articles) > 0));
    }

    /**
     * Get all accessible KB content (categories + articles from selected categories).
     */
    /**
     * @return array<string, Collection<int, KbCategory|KbArticle>>
     */
    /**
     * @return array{
     *     categories: Collection<int, KbCategory>,
     *     articles: Collection<int, KbArticle>
     * }
     */
    public function getAccessibleKbContent(): array
    {
        $accessibleContent = [
            'categories' => new Collection(),
            'articles' => new Collection(),
        ];
        // Get selected categories
        if (is_array($this->kb_categories) && count($this->kb_categories) > 0) {
            $categories = KbCategory::whereIn('id', $this->kb_categories)->get();
            $accessibleContent['categories'] = $categories;
            // Get all articles from selected categories
            $articles = KbArticle::whereIn('kb_category_id', $this->kb_categories)->get();
            $accessibleContent['articles'] = $articles;
        }
        // Add specifically selected articles
        if (is_array($this->kb_articles) && count($this->kb_articles) > 0) {
            $specificArticles = KbArticle::whereIn('id', $this->kb_articles)->get();
            $accessibleContent['articles'] = $accessibleContent['articles']->merge($specificArticles)->unique('id');
        }

        return $accessibleContent;
    }

    /**
     * Check if support is still active for a license.
     */
    public function isSupportActive(Carbon $licenseCreatedAt): bool
    {
        return $licenseCreatedAt->copy()->addDays($this->support_days)->isFuture();
    }

    /**
     * Get extended support price formatted.
     */
    public function getFormattedExtendedSupportPriceAttribute(): string
    {
        $price = $this->extended_support_price ?? 0.0;

        return '$' . number_format((float)$price, 2);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format((float)$this->price, 2);
    }

    /**
     * Get formatted renewal price.
     */
    public function getFormattedRenewalPriceAttribute(): string
    {
        return $this->renewal_price ? '$' . number_format((float)$this->renewal_price, 2) : 'N/A';
    }

    /**
     * Get renewal period label.
     */
    public function getRenewalPeriodLabelAttribute(): string
    {
        return match ($this->renewal_period ?? 'annual') {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi-annual' => 'Semi-Annual',
            'annual' => 'Annual',
            'three-years' => '3 Years',
            'lifetime' => 'Lifetime',
            default => 'Annual',
        };
    }

    /**
     * Get renewal period label (method version for view compatibility).
     */
    public function renewalPeriodLabel(): string
    {
        $label = $this->renewal_period_label ?? 'Annual';

        return is_string($label) ? $label : 'Annual';
    }

    /**
     * Check if product is in stock.
     */
    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if product has limited stock.
     */
    public function hasLimitedStock(): bool
    {
        return ! is_null($this->stock) && (int)$this->stock > 0;
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return is_null($this->stock) || (int)$this->stock > 0;
    }

    /**
     * Decrease stock by specified amount.
     */
    public function decreaseStock(int $amount = 1): void
    {
        if ($this->hasLimitedStock()) {
            $this->decrement('stock', $amount);
        }
    }

    /**
     * Increase stock by specified amount.
     */
    public function increaseStock(int $amount = 1): void
    {
        if ($this->hasLimitedStock()) {
            $this->increment('stock', $amount);
        }
    }

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute(): string
    {
        if (is_null($this->stock)) {
            return 'Unlimited';
        }

        return (int)$this->stock > 0 ? 'In Stock (' . $this->stock . ')' : 'Out of Stock';
    }

    /**
     * Get renewal price (fallback to regular price if not set).
     */
    public function getRenewalPrice(): float
    {
        return (float)($this->renewal_price ?? $this->price);
    }

    /**
     * Calculate next renewal date.
     */
    public function getNextRenewalDate(Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?? now();

        return $fromDate->copy()->addDays($this->duration_days);
    }

    /**
     * Calculate tax amount.
     */
    public function getTaxAmountAttribute(): float
    {
        return (float)$this->price * ((float)$this->tax_rate / 100);
    }

    /**
     * Get total price including tax.
     */
    public function getTotalPriceAttribute(): float
    {
        return (float)$this->price + (float)$this->tax_amount;
    }

    /**
     * Get the latest version for this product (from updates or base version).
     */
    public function getLatestVersionAttribute(): string
    {
        // Check if there are any product updates available
        $latestUpdate = $this->updates()
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();
        if ($latestUpdate) {
            // Return the latest update version
            return (string)($latestUpdate->version ?? '1.0');
        }

        // If no updates available, return the base product version
        return (string)($this->version ?? '1.0');
    }
}
