<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $product_id
 * @property string $original_name
 * @property string $encrypted_name
 * @property string $file_path
 * @property string $file_type
 * @property int $file_size
 * @property string $encryption_key
 * @property string $checksum
 * @property string|null $description
 * @property int $download_count
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_extension
 * @property-read mixed $formatted_size
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile active()
 * @method static \Database\Factories\ProductFileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile forProduct($productId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereDownloadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereEncryptedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereEncryptionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        $unitsCount = count($units);
        for ($i = 0; $bytes > 1024 && $i < $unitsCount - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
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
            \Log::error('Failed to decrypt file: ' . $e->getMessage());
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
