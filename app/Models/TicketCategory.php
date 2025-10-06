<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_keywords
 * @property string|null $meta_description
 * @property string|null $icon
 * @property string $priority
 * @property string $color
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $requires_login
 * @property bool $requires_valid_purchase_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory active()
 * @method static \Database\Factories\TicketCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereRequiresLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereRequiresValidPurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TicketCategory extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = TicketCategoryFactory::class;

    protected $table = 'ticket_categories';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'sort_order',
        'is_active',
        'requires_login',
        'requires_valid_purchase_code',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'icon',
        'priority',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'requires_login' => 'boolean',
        'requires_valid_purchase_code' => 'boolean',
    ];
    /**
     * Get the tickets for this category.
     */
    /**
     * @return HasMany<Ticket, TicketCategory>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
    /**
     * Scope to get only active categories.
     */
    /**
     * @param Builder<TicketCategory> $query
     * @return Builder<TicketCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    /**
     * Scope to order by sort_order.
     */
    /**
     * @param Builder<TicketCategory> $query
     * @return Builder<TicketCategory>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
