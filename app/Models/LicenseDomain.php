<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class LicenseDomain extends Model
{
    use HasFactory;
    protected $fillable = [
        'license_id', 'domain', 'status', 'is_verified', 'verified_at', 'added_at', 'last_used_at',
    ];
    protected $casts = [
        'license_id' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'added_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];
    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
