<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model
{
    use HasFactory;
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
    public function licenses()
    {
        return $this->hasMany(License::class);
    }
    /**
     * Get product files.
     */
    public function files()
    {
        return $this->hasMany(ProductFile::class);
    }
    /**
     * Get the product updates.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(ProductUpdate::class);
    }
    /**
     * Get the latest update for this product.
     */
    public function latestUpdate()
    {
        return $this->hasOne(ProductUpdate::class)->active()->latest('version');
    }
    /**
     * Get the current version of the product.
     */
    public function getCurrentVersionAttribute(): string
    {
        return $this->latestUpdate?->version ?? $this->version ?? '1.0.0';
    }
    /**
     * Get active product files.
     */
    public function activeFiles()
    {
        return $this->hasMany(ProductFile::class)->where('is_active', true);
    }
    public function programmingLanguage()
    {
        return $this->belongsTo(ProgrammingLanguage::class, 'programming_language');
    }
    public function category()
    {
        // Specify foreign key explicitly to avoid default 'product_category_id'
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    /**
     * Get KB categories linked to this product.
     */
    public function kbCategories()
    {
        return $this->belongsToMany(KbCategory::class, 'product_kb_categories', 'product_id', 'kb_category_id');
    }
    /**
     * Get KB articles linked to this product.
     */
    public function kbArticles()
    {
        return $this->belongsToMany(KbArticle::class, 'product_kb_articles', 'product_id', 'kb_article_id');
    }
    /**
     * Get KB categories by IDs stored in JSON.
     */
    public function getKbCategoriesAttribute($value)
    {
        if (! $value) {
            return [];
        }
        $categoryIds = json_decode($value, true) ?? [];
        return $categoryIds; // Return array of IDs, not Collection
    }
    /**
     * Get KB articles by IDs stored in JSON.
     */
    public function getKbArticlesAttribute($value)
    {
        if (! $value) {
            return [];
        }
        $articleIds = json_decode($value, true) ?? [];
        return $articleIds; // Return array of IDs, not Collection
    }
    /**
     * Get KB categories as Collection.
     */
    public function getKbCategoriesCollection()
    {
        if (! $this->kb_categories) {
            return collect();
        }
        return KbCategory::whereIn('id', $this->kb_categories)->get();
    }
    /**
     * Get KB articles as Collection.
     */
    public function getKbArticlesCollection()
    {
        if (! $this->kb_articles) {
            return collect();
        }
        return KbArticle::whereIn('id', $this->kb_articles)->get();
    }
    /**
     * Check if product has KB access requirements.
     */
    public function hasKbAccess()
    {
        return $this->kb_access_required &&
               (($this->kb_categories && count($this->kb_categories) > 0) ||
                ($this->kb_articles && count($this->kb_articles) > 0));
    }
    /**
     * Get all accessible KB content (categories + articles from selected categories).
     */
    public function getAccessibleKbContent()
    {
        $accessibleContent = [
            'categories' => [],
            'articles' => [],
        ];
        // Get selected categories
        if ($this->kb_categories && count($this->kb_categories) > 0) {
            $accessibleContent['categories'] = KbCategory::whereIn('id', $this->kb_categories)->get();
            // Get all articles from selected categories
            $accessibleContent['articles'] = KbArticle::whereIn('kb_category_id', $this->kb_categories)->get();
        }
        // Add specifically selected articles
        if ($this->kb_articles && count($this->kb_articles) > 0) {
            $specificArticles = KbArticle::whereIn('id', $this->kb_articles)->get();
            $accessibleContent['articles'] = $accessibleContent['articles']->merge($specificArticles)->unique('id');
        }
        return $accessibleContent;
    }
    /**
     * Check if support is still active for a license.
     */
    public function isSupportActive($licenseCreatedAt)
    {
        return $licenseCreatedAt->copy()->addDays($this->support_days)->isFuture();
    }
    /**
     * Get extended support price formatted.
     */
    public function getFormattedExtendedSupportPriceAttribute()
    {
        return '$'.number_format($this->extended_support_price, 2);
    }
    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return '$'.number_format($this->price, 2);
    }
    /**
     * Get formatted renewal price.
     */
    public function getFormattedRenewalPriceAttribute()
    {
        return $this->renewal_price ? '$'.number_format($this->renewal_price, 2) : 'N/A';
    }
    /**
     * Get renewal period label.
     */
    public function getRenewalPeriodLabelAttribute()
    {
        return match ($this->renewal_period) {
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
    public function renewalPeriodLabel()
    {
        return $this->renewal_period_label;
    }
    /**
     * Check if product is in stock.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    /**
     * Check if product has limited stock.
     */
    public function hasLimitedStock()
    {
        return ! is_null($this->stock) && $this->stock > 0;
    }
    /**
     * Check if product is in stock.
     */
    public function isInStock()
    {
        return is_null($this->stock) || $this->stock > 0;
    }
    /**
     * Decrease stock by specified amount.
     */
    public function decreaseStock($amount = 1)
    {
        if ($this->hasLimitedStock()) {
            $this->decrement('stock', $amount);
        }
    }
    /**
     * Increase stock by specified amount.
     */
    public function increaseStock($amount = 1)
    {
        if ($this->hasLimitedStock()) {
            $this->increment('stock', $amount);
        }
    }
    /**
     * Get stock status.
     */
    public function getStockStatusAttribute()
    {
        if (is_null($this->stock)) {
            return 'Unlimited';
        }
        return $this->stock > 0 ? 'In Stock ('.$this->stock.')' : 'Out of Stock';
    }
    /**
     * Get renewal price (fallback to regular price if not set).
     */
    public function getRenewalPrice()
    {
        return $this->renewal_price ?? $this->price;
    }
    /**
     * Calculate next renewal date.
     */
    public function getNextRenewalDate($fromDate = null)
    {
        $fromDate = $fromDate ?? now();
        return $fromDate->copy()->addDays($this->duration_days);
    }
    /**
     * Calculate tax amount.
     */
    public function getTaxAmountAttribute()
    {
        return $this->price * ($this->tax_rate / 100);
    }
    /**
     * Get total price including tax.
     */
    public function getTotalPriceAttribute()
    {
        return $this->price + $this->tax_amount;
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
            return $latestUpdate->version;
        }
        // If no updates available, return the base product version
        return $this->version ?? '1.0';
    }
}
