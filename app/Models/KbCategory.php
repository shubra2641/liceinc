<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $serial
 * @property bool $requires_serial
 * @property string|null $serial_message
 * @property int $sortOrder
 * @property int $is_published
 * @property bool $is_featured
 * @property bool $isActive
 * @property int|null $parent_id
 * @property int|null $productId
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KbArticle> $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, KbCategory> $children
 * @property-read int|null $children_count
 * @property-read KbCategory|null $parent
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory active()
 * @method static \Database\Factories\KbCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereRequiresSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereSerialMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KbCategory extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = KbCategoryFactory::class;

    protected $fillable = [
        'name', 'slug', 'description', 'parent_id', 'serial', 'requires_serial', 'serial_message',
        'sortOrder', 'productId', 'meta_title', 'meta_description', 'meta_keywords', 'icon',
        'is_featured', 'isActive',
    ];
    protected $casts = [
        'parent_id' => 'integer',
        'requires_serial' => 'boolean',
        'is_featured' => 'boolean',
        'isActive' => 'boolean',
        'sortOrder' => 'integer',
    ];
    /**
     * @return BelongsTo<KbCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'parent_id');
    }
    /**
     * @return HasMany<KbCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(KbCategory::class, 'parent_id');
    }
    /**
     * @return HasMany<KbArticle, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(KbArticle::class, 'kbCategory_id');
    }
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Scope to get only active categories.
     *
     * @param Builder<KbCategory> $query
     * @return Builder<KbCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isActive', true);
    }
}
