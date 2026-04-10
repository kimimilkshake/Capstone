<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notification';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'cargo_receipt_id',
        'payment_id',
        'booking_ref_no',
        'notification_message',
        'notification_type',
        'notification_status',
        'notification_created'
    ];

    protected $casts = [
        'notification_created' => 'datetime',
    ];

    // Relationships
    public function cargoReceipt()
    {
        return $this->belongsTo(CargoReceipt::class, 'cargo_receipt_id', 'cargo_receipt_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_ref_no', 'booking_ref_no');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNotIn('notification_status', ['Read', 'Archived']);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('notification_created', 'desc')->limit($limit);
    }
}
