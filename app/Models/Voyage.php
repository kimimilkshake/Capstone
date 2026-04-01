<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voyage extends Model
{
    use HasFactory;

    protected $table = 'voyage';
    protected $primaryKey = 'voyage_id';
    public $timestamps = true;

    protected $fillable = [
        'vessel_id',
        'route_port_id',
        'voyage_departure_date',
        'voyage_arrival_date',
        'voyage_estimated_TD',
        'voyage_estimated_TA',
        'voyage_actual_departure_date',
        'voyage_actual_arrival_date',
        'voyage_actual_TD',
        'voyage_actual_TA',
        'voyage_status',
        'voyage_description',
        'voyage_code'
    ];

    public function vessel()
    {
        return $this->belongsTo(Vessel::class, 'vessel_id', 'vessel_id');
    }

    public function routePort()
    {
        return $this->belongsTo(RoutePort::class, 'route_port_id', 'route_port_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'voyage_id', 'voyage_id');
    }

    public function passengerBookings()
    {
        return $this->hasMany(Booking::class, 'voyage_id')->where('booking_type', 'passenger');
    }

    public function cargoBookings()
    {
        return $this->hasMany(CargoBooking::class, 'voyage_id');
    }

    // New: cargoReceipts uses the cargo_receipt.voyage_id column (which exists)
    public function cargoReceipts()
    {
        return $this->hasMany(\App\Models\CargoReceipt::class, 'voyage_id', 'voyage_id');
    }
    public function passengerTickets()
    {
        return $this->hasMany(PassengerTicket::class, 'voyage_id');
    }
}