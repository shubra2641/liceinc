<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $productId
 * @property string $originalName
 * @property string $encrypted_name
 * @property string $filePath
 * @property string $fileType
 * @property int $fileSize
 * @property string $encryptionKey
 * @property string $checksum
 * @property string|null $description
 * @property int $downloadCount
 * @property bool $isActive
 * @property array<array-key, mixed>|null $update_info
 * @property bool $is_update
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read mixed $fileExtension
 * @property-read mixed $formattedSize
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
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = ProductFileFactory::class;

    protected $fillable = [
        'productId',
        'originalName',
        'encrypted_name',
        'filePath',
        'fileType',
        'fileSize',
        'encryptionKey',
        'checksum',
        'description',
        'downloadCount',
        'isActive',
    ];
    protected $casts = [
        'fileSize' => 'integer',
        'downloadCount' => 'integer',
        'isActive' => 'boolean',
    ];
    protected $hidden = [
        'encryptionKey',
    ];
    public $timestamps = true;
    /**
     * Get the product that owns the file.
     */
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, $this>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->fileSize;
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
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }
    /**
     * Check if file exists in storage.
     */
    public function fileExists(): bool
    {
        return Storage::disk('private')->exists($this->filePath);
    }
    /**
     * Get decrypted file content.
     */
    public function getDecryptedContent(): ?string
    {
        if (! $this->fileExists()) {
            return null;
        }
        try {
            $encryptedContent = Storage::disk('private')->get($this->filePath);
            if ($encryptedContent === null) {
                return null;
            }
            $decryptionKey = Crypt::decryptString($this->encryptionKey);
            $result = openssl_decrypt(
                $encryptedContent,
                'AES-256-CBC',
                $decryptionKey,
                0,
                substr(hash('sha256', $decryptionKey), 0, 16),
            );
            return $result !== false ? $result : null;
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt file: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Increment download count.
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('downloadCount');
    }
    /**
     * Scope for active files.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductFile> $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<ProductFile>
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }
    /**
     * Scope for files belonging to a product.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductFile> $query
     * @param int $productId
     *
     * @return \Illuminate\Database\Eloquent\Builder<ProductFile>
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('productId', $productId);
    }
}
