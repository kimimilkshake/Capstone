<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    use HasFactory;

    // Specify the table name explicitly
    protected $table = 'booking';

    // If your primary key is BIGINT and auto-increment
    protected $primaryKey = 'booking_ref_no';
    public $incrementing = true; // auto-increment
    protected $keyType = 'int';  // BIGINT stored as integer

    protected $fillable = [
        'booking_type',
        'booking_status',
        'booking_date',
        'voyage_id',
        'sender_id',
        'consignee_id',
        'cargo_item_id',
        'cargo_item_qty',
        'payment_id',
    ];
    // Booking.php
    public function cargoBookings()
    {
        return $this->hasMany(CargoBooking::class, 'booking_ref_no', 'booking_ref_no');
    }

    // Human-friendly booking code (e.g., CBBK26001 for first cargo booking in 2026)
    public function getBookingCodeAttribute()
    {
        if (strcasecmp((string) $this->booking_type, 'cargo') === 0) {
            $bookingDate = $this->booking_date ?? $this->created_at ?? now();
            $bookingYear = Carbon::parse($bookingDate)->year;
            $yearSuffix = Carbon::parse($bookingDate)->format('y');

            $yearlySequence = static::query()
                ->whereRaw('LOWER(booking_type) = ?', ['cargo'])
                ->whereYear(DB::raw('COALESCE(booking_date, created_at)'), $bookingYear)
                ->where('booking_ref_no', '<=', $this->booking_ref_no)
                ->count();

            return 'CBBK' . $yearSuffix . str_pad((string) $yearlySequence, 3, '0', STR_PAD_LEFT);
        }
        // Passenger bookings use just the raw number
        return $this->booking_ref_no;
    }

    // Relations
    public function sender()
    {
        return $this->belongsTo(Sender::class, 'sender_id', 'sender_id');
    }

    public function consignee()
    {
        return $this->belongsTo(Consignee::class, 'consignee_id', 'consignee_id');
    }

    public function voyage()
    {
        return $this->belongsTo(Voyage::class, 'voyage_id', 'voyage_id');
    }

    public function cargoItem()
    {
        return $this->belongsTo(CargoItem::class, 'cargo_item_id', 'cargo_item_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_ref_no', 'booking_ref_no');
    }

    public function passengerTickets()
    {
        return $this->hasMany(PassengerTicket::class, 'booking_ref_no', 'booking_ref_no');
    }

}
