<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
