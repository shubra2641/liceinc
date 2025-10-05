<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
class ProductUpdate extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
            if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
                return false;
            }
        }
        // Check Laravel version
        if (isset($requirements['laravel_version'])) {
            $laravelVersion = app()->version();
            if (version_compare($laravelVersion, $requirements['laravel_version'], '<')) {
                return false;
            }
        }
        // Check extensions
        if (isset($requirements['extensions'])) {
            foreach ($requirements['extensions'] as $extension) {
                if (! extension_loaded($extension)) {
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
        if (! $this->changelog || ! is_array($this->changelog)) {
            return '';
        }
        return implode("\n", $this->changelog);
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
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2).' '.$units[$i];
    }
    /**
     * Scope for active updates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Scope for major updates.
     */
    public function scopeMajor($query)
    {
        return $query->where('is_major', true);
    }
    /**
     * Scope for required updates.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
    /**
     * Scope for updates newer than version.
     */
    public function scopeNewerThan($query, string $version)
    {
        return $query->whereRaw('version > ?', [$version]);
    }
}
