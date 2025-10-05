<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Invoice extends Model
{
    use HasFactory;
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function license()
    {
        return $this->belongsTo(License::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('status', 'pending');
    }
    // معالجات
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }
    /**
     * توليد رقم فاتورة فريد.
     */
    public static function generateInvoiceNumber()
    {
        do {
            $number = 'INV-'.date('Y').'-'.strtoupper(Str::random(8));
        } while (static::where('invoice_number', $number)->exists());
        return $number;
    }
    /**
     * التحقق من انتهاء صلاحية الفاتورة.
     */
    public function isOverdue()
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }
    /**
     * تحديث حالة الفاتورة إلى مدفوعة.
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
    /**
     * تحديث حالة الفاتورة إلى متأخرة.
     */
    public function markAsOverdue()
    {
        $this->update(['status' => 'overdue']);
    }
    /**
     * إلغاء الفاتورة.
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
    /**
     * الحصول على المبلغ المتبقي.
     */
    public function getRemainingAmountAttribute()
    {
        return $this->status === 'paid' ? 0 : $this->amount;
    }
    /**
     * الحصول على عدد الأيام المتبقية حتى تاريخ الاستحقاق
     */
    public function getDaysUntilDueAttribute()
    {
        return now()->diffInDays($this->due_date, false);
    }
}
