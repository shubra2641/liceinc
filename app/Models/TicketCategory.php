<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TicketCategory extends Model
{
    use HasFactory;
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
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
