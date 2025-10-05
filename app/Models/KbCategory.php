<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
