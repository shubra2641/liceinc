<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $serial
 * @property bool $requires_serial
 * @property string|null $serial_message
 * @property int $sort_order
 * @property int $is_published
 * @property bool $is_featured
 * @property bool $is_active
 * @property int|null $parent_id
 * @property int|null $product_id
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'parent_id', 'serial', 'requires_serial', 'serial_message',
        'sort_order', 'product_id', 'meta_title', 'meta_description', 'meta_keywords', 'icon',
        'is_featured', 'is_active',
    ];
    protected $casts = [
        'parent_id' => 'integer',
        'requires_serial' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
    public function parent()
    {
        return $this->belongsTo(KbCategory::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(KbCategory::class, 'parent_id');
    }
    public function articles()
    {
        return $this->hasMany(KbArticle::class, 'kb_category_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
