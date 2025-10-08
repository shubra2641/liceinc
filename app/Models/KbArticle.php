<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $kb_category_id
 * @property int|null $product_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $image
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string $content
 * @property string|null $serial
 * @property int $requires_serial
 * @property string|null $serial_message
 * @property int $sort_order
 * @property int $views
 * @property bool $is_published
 * @property bool $allow_comments
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read KbCategory $category
 * @property-read Product|null $product
 *
 * @method static \Database\Factories\KbArticleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereAllowComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereKbCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereRequiresSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereSerialMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KbArticle whereViews($value)
 *
 * @mixin \Eloquent
 */
class KbArticle extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = KbArticleFactory::class;

    protected $fillable = [
        'kb_category_id', 'title', 'slug', 'excerpt', 'content', 'views', 'is_published',
        'serial', 'requires_serial', 'serial_message', 'image', 'meta_title', 'meta_description', 'meta_keywords',
        'allow_comments', 'is_featured',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'allow_comments' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * @return BelongsTo<KbCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'kb_category_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @param Builder<KbArticle> $query
     *
     * @return Builder<KbArticle>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
