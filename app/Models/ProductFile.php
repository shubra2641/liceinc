<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
class ProductFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'original_name',
        'encrypted_name',
        'file_path',
        'file_type',
        'file_size',
        'encryption_key',
        'checksum',
        'description',
        'download_count',
        'is_active',
    ];
    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
        'is_active' => 'boolean',
    ];
    protected $hidden = [
        'encryption_key',
    ];
    public $timestamps = true;
    /**
     * Get the product that owns the file.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2).' '.$units[$i];
    }
    /**
     * Get file extension.
     */
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }
    /**
     * Check if file exists in storage.
     */
    public function fileExists()
    {
        return Storage::disk('private')->exists($this->file_path);
    }
    /**
     * Get decrypted file content.
     */
    public function getDecryptedContent()
    {
        if (! $this->fileExists()) {
            return null;
        }
        try {
            $encryptedContent = Storage::disk('private')->get($this->file_path);
            $decryptionKey = Crypt::decryptString($this->encryption_key);
            return openssl_decrypt(
                $encryptedContent,
                'AES-256-CBC',
                $decryptionKey,
                0,
                substr(hash('sha256', $decryptionKey), 0, 16),
            );
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt file: '.$e->getMessage());
            return null;
        }
    }
    /**
     * Increment download count.
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
    /**
     * Scope for active files.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Scope for files belonging to a product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
