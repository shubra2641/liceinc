<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_keywords
 * @property string|null $meta_description
 * @property string|null $color
 * @property string|null $text_color
 * @property string|null $icon
 * @property string|null $image
 * @property bool $is_active
 * @property bool $show_in_menu
 * @property bool $is_featured
 * @property bool $allow_subcategories
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductCategory> $children
 * @property-read int|null $children_count
 * @property-read ProductCategory|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory active()
 * @method static \Database\Factories\ProductCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory menuVisible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory roots()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereAllowSubcategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereShowInMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereTextColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductCategory extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = ProductCategoryFactory::class;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
        'parent_id',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'color',
        'text_color',
        'icon',
        'show_in_menu',
        'is_featured',
        'allow_subcategories',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'is_featured' => 'boolean',
        'allow_subcategories' => 'boolean',
    ];
    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
    /**
     * Get the parent category.
     */
    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }
    /**
     * Get the child categories.
     */
    /**
     * @return HasMany<ProductCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }
    /**
     * Scope a query to only include root categories (no parent).
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductCategory> $query
     * @return \Illuminate\Database\Eloquent\Builder<ProductCategory>
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
    /**
     * Scope a query to only include active categories.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductCategory> $query
     * @return \Illuminate\Database\Eloquent\Builder<ProductCategory>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Scope a query to only include categories that show in menu.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductCategory> $query
     * @return \Illuminate\Database\Eloquent\Builder<ProductCategory>
     */
    public function scopeMenuVisible($query)
    {
        return $query->where('show_in_menu', true);
    }
    /**
     * Scope a query to only include featured categories.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductCategory> $query
     * @return \Illuminate\Database\Eloquent\Builder<ProductCategory>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function (ProductCategory $category) {
            if (empty($category->slug)) {
                $categoryName = $category->name ?? '';
                $category->slug = Str::slug($categoryName);
            }
        });
        static::updating(function (ProductCategory $category) {
            if ($category->isDirty('name')) {
                $categoryName = $category->name ?? '';
                $category->slug = Str::slug($categoryName);
            }
        });
    }
}
