<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $invoiceNumber
 * @property string|null $order_number
 * @property string|null $payment_gateway
 * @property int $userId
 * @property int|null $licenseId
 * @property int|null $productId
 * @property string $type
 * @property numeric $amount
 * @property string $currency
 * @property string $status
 * @property \Illuminate\Support\Carbon $dueDate
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string|null $notes
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read mixed $days_until_due
 * @property-read mixed $remaining_amount
 * @property-read \App\Models\License|null $license
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice dueSoon($days = 7)
 * @method static \Database\Factories\InvoiceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUserId($value)
 * @mixin \Eloquent
 */
class Invoice extends Model
{
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = InvoiceFactory::class;

    protected $fillable = [
        'invoiceNumber',
        'userId',
        'licenseId',
        'productId',
        'type',
        'amount',
        'currency',
        'status',
        'dueDate',
        'paid_at',
        'notes',
        'metadata',
    ];
    protected $casts = [
        'dueDate' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];
    // العلاقات
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    // Scopes
    /**
     * @param Builder<Invoice> $query
     *
     * @return Builder<Invoice>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
    /**
     * @param Builder<Invoice> $query
     *
     * @return Builder<Invoice>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }
    /**
     * @param Builder<Invoice> $query
     *
     * @return Builder<Invoice>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue');
    }
    /**
     * @param Builder<Invoice> $query
     *
     * @return Builder<Invoice>
     */
    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->where('dueDate', '<=', now()->addDays($days))
            ->where('status', 'pending');
    }
    // معالجات
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            if (is_object($invoice) && property_exists($invoice, 'invoiceNumber') && empty($invoice->invoiceNumber)) {
                $invoice->invoiceNumber = static::generateInvoiceNumber();
            }
        });
    }
    /**
     * توليد رقم فاتورة فريد.
     */
    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('Y') . '-' . strtoupper(Str::random(8));
        } while (static::where('invoiceNumber', $number)->exists());
        return $number;
    }
    /**
     * التحقق من انتهاء صلاحية الفاتورة.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->dueDate->isPast();
    }
    /**
     * تحديث حالة الفاتورة إلى مدفوعة.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
    /**
     * تحديث حالة الفاتورة إلى متأخرة.
     */
    public function markAsOverdue(): void
    {
        $this->update(['status' => 'overdue']);
    }
    /**
     * إلغاء الفاتورة.
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
    /**
     * الحصول على المبلغ المتبقي.
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->status === 'paid' ? 0.0 : (float)$this->amount;
    }
    /**
     * الحصول على عدد الأيام المتبقية حتى تاريخ الاستحقاق
     */
    public function getDaysUntilDueAttribute(): int
    {
        return (int)now()->diffInDays($this->dueDate, false);
    }
}
