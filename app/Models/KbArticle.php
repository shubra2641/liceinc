<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class KbArticle extends Model
{
    use HasFactory;
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
    public function category()
    {
        return $this->belongsTo(KbCategory::class, 'kb_category_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
