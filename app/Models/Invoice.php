<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property int $id * @property string $invoice_number * @property string|null $order_number * @property string|null $payment_gateway * @property int $user_id * @property int|null $license_id * @property int|null $product_id * @property string $type * @property numeric $amount * @property string $currency * @property string $status * @property \Illuminate\Support\Carbon $due_date * @property \Illuminate\Support\Carbon|null $paid_at * @property string|null $notes * @property array<array-key, mixed>|null $metadata * @property \Illuminate\Support\Carbon|null $created_at * @property \Illuminate\Support\Carbon|null $updated_at * @property-read mixed $days_until_due * @property-read mixed $remaining_amount * @property-read \App\Models\License|null $license * @property-read \App\Models\Product|null $product * @property-read \App\Models\User $user * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice dueSoon($days = 7) * @method static \Database\Factories\InvoiceFactory factory($count = null, $state = []) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery() * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery() * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice overdue() * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice paid() * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice pending() * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query() * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAmount($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCurrency($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereLicenseId($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMetadata($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNotes($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaidAt($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereProductId($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereType($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value) * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUserId($value) * @mixin \Eloquent */
class Invoice extends Model
{
    /**   * @phpstan-ignore-next-line */
    use HasFactory;

    /**   * @phpstan-ignore-next-line */
    protected static $factory = InvoiceFactory::class;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'license_id',
        'product_id',
        'type',
        'amount',
        'currency',
        'status',
        'due_date',
        'paid_at',
        'notes',
        'metadata',
    ];
    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];
    // العلاقات
    /**   * @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**   * @return BelongsTo<License, $this> */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
    /**   * @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    // Scopes
    /**   * @param Builder<Invoice> $query * @return Builder<Invoice> */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
    /**   * @param Builder<Invoice> $query * @return Builder<Invoice> */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }
    /**   * @param Builder<Invoice> $query * @return Builder<Invoice> */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue');
    }
    /**   * @param Builder<Invoice> $query * @return Builder<Invoice> */
    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('status', 'pending');
    }
    // معالجات
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            if (is_object($invoice) && property_exists($invoice, 'invoice_number') && empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }
    /**   * توليد رقم فاتورة فريد. */
    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('Y') . '-' . strtoupper(Str::random(8));
        } while (static::where('invoice_number', $number)->exists());
        return $number;
    }
    /**   * التحقق من انتهاء صلاحية الفاتورة. */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }
    /**   * تحديث حالة الفاتورة إلى مدفوعة. */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
    /**   * تحديث حالة الفاتورة إلى متأخرة. */
    public function markAsOverdue(): void
    {
        $this->update(['status' => 'overdue']);
    }
    /**   * إلغاء الفاتورة. */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
    /**   * الحصول على المبلغ المتبقي. */
    public function getRemainingAmountAttribute(): float
    {
        return $this->status === 'paid' ? 0.0 : (float)$this->amount;
    }
    /**   * الحصول على عدد الأيام المتبقية حتى تاريخ الاستحقاق */
    public function getDaysUntilDueAttribute(): int
    {
        return (int)now()->diffInDays($this->due_date, false);
    }
}
