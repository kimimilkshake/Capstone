<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerTicket extends Model
{
    use HasFactory;

    protected $table = 'passenger_ticket';
    protected $primaryKey = 'passenger_ticket_id';

    protected $fillable = [
        'passenger_id',
        'voyage_id',
        'promo_id',
        'payment_id',
        'booking_ref_no',
        'pt_valid_until',
        'pt_cot_no',
        'pt_ticket_price',
    ];

    protected $casts = [
        'pt_valid_until' => 'date',
        'pt_ticket_price' => 'decimal:2',
    ];

    // Relationships
    public function passenger()
    {
        return $this->belongsTo(Passenger::class, 'passenger_id', 'passenger_id');
    }

    public function voyage()
    {
        return $this->belongsTo(Voyage::class, 'voyage_id', 'voyage_id');
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class, 'promo_id', 'promo_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_ref_no', 'booking_ref_no');
    }
}
