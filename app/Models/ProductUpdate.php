<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $product_id
 * @property string $version
 * @property string $title
 * @property string|null $description
 * @property array<array-key, mixed>|null $changelog
 * @property string|null $file_path
 * @property string|null $file_name
 * @property int|null $file_size
 * @property string|null $file_hash
 * @property string|null $update_file_path
 * @property bool $is_major
 * @property bool $is_required
 * @property bool $is_active
 * @property array<array-key, mixed>|null $requirements
 * @property array<array-key, mixed>|null $compatibility
 * @property \Illuminate\Support\Carbon|null $released_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $changelog_text
 * @property-read string|null $download_url
 * @property-read string|null $file_url
 * @property-read string $formatted_file_size
 * @property-read Product $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate major()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate newerThan(string $version)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate required()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereChangelog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereCompatibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereFileHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereIsMajor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereReleasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpdate whereVersion($value)
 *
 * @mixin \Eloquent
 */
class ProductUpdate extends Model
{
    /**
     * @phpstan-ignore-next-line
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'version',
        'title',
        'description',
        'changelog',
        'file_path',
        'file_name',
        'file_size',
        'file_hash',
        'is_major',
        'is_required',
        'is_active',
        'requirements',
        'compatibility',
        'released_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_major' => 'boolean',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'changelog' => 'array',
        'requirements' => 'array',
        'compatibility' => 'array',
        'released_at' => 'datetime',
    ];

    /**
     * Get the product that owns the update.
     */
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the update file URL.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Get the update file download URL.
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return route('api.product-updates.download', [
            'product' => $this->product_id,
            'version' => $this->version,
        ]);
    }

    /**
     * Check if this update is newer than given version.
     */
    public function isNewerThan(string $version): bool
    {
        return version_compare($this->version, $version, '>');
    }

    /**
     * Check if this update is compatible with given version.
     */
    public function isCompatibleWith(string $version): bool
    {
        if (! $this->compatibility) {
            return true;
        }

        return in_array($version, $this->compatibility);
    }

    /**
     * Check if system meets requirements.
     */
    public function meetsRequirements(): bool
    {
        if (! $this->requirements) {
            return true;
        }
        $requirements = $this->requirements;
        // Check PHP version
        if (isset($requirements['php_version'])) {
            $phpVersion = $requirements['php_version'];
            if (is_string($phpVersion) && version_compare(PHP_VERSION, $phpVersion, '<')) {
                return false;
            }
        }
        // Check Laravel version
        if (isset($requirements['laravel_version'])) {
            $laravelVersion = app()->version();
            $requiredVersion = is_string($requirements['laravel_version']) ? $requirements['laravel_version'] : '';
            if (version_compare($laravelVersion, $requiredVersion, '<')) {
                return false;
            }
        }
        // Check extensions
        if (isset($requirements['extensions']) && is_array($requirements['extensions'])) {
            foreach ($requirements['extensions'] as $extension) {
                if (! extension_loaded(is_string($extension) ? $extension : '')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get changelog as text (for editing).
     */
    public function getChangelogTextAttribute(): string
    {
        $changelog = $this->changelog;
        if (empty($changelog)) {
            return '';
        }

        // Changelog is already validated
        return implode("\n", $changelog);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (! $this->file_size) {
            return 'Unknown';
        }
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitsCount = count($units);
        for ($i = 0; $bytes > 1024 && $i < $unitsCount - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Scope for active updates.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductUpdate> $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<ProductUpdate>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for major updates.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductUpdate> $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<ProductUpdate>
     */
    public function scopeMajor($query)
    {
        return $query->where('is_major', true);
    }

    /**
     * Scope for required updates.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductUpdate> $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<ProductUpdate>
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for updates newer than version.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<ProductUpdate> $query
     * @param string $version
     *
     * @return \Illuminate\Database\Eloquent\Builder<ProductUpdate>
     */
    public function scopeNewerThan($query, string $version)
    {
        return $query->whereRaw('version > ?', [$version]);
    }
}
