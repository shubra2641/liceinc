<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $subject
 * @property string $priority
 * @property string $status
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $license_id
 * @property int|null $invoice_id
 * @property string|null $purchase_code
 * @property int|null $category_id
 * @property-read \App\Models\TicketCategory|null $category
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\License|null $license
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketReply> $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\TicketFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket wherePurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUserId($value)
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'license_id',
        'invoice_id',
        'category_id',
        'purchase_code',
        'subject',
        'priority',
        'status',
        'content',
    ];
    protected $casts = [
        'user_id' => 'integer',
        'license_id' => 'integer',
        'category_id' => 'integer',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function license()
    {
        return $this->belongsTo(License::class);
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }
    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }
}
