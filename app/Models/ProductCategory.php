<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class ProductCategory extends Model
{
    use HasFactory;
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
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }
    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }
    /**
     * Scope a query to only include root categories (no parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Scope a query to only include categories that show in menu.
     */
    public function scopeMenuVisible($query)
    {
        return $query->where('show_in_menu', true);
    }
    /**
     * Scope a query to only include featured categories.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
