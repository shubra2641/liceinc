<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TicketReply extends Model
{
    use HasFactory;
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
    ];
    protected $casts = [
        'ticket_id' => 'integer',
        'user_id' => 'integer',
    ];
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
