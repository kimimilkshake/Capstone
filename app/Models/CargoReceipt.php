<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoReceipt extends Model
{
    use HasFactory;

    protected $table = 'cargo_receipt';
    protected $primaryKey = 'cargo_receipt_id';

    protected $fillable = [
        'booking_ref_no',
        'cargo_booking_id',
        'sender_id',
        'consignee_id',
        'cargo_item_id',
        'voyage_id',
        'cargo_item_qty',
        'arrastre',
        'total'
    ];

    // Relationship to Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_ref_no', 'booking_ref_no');
    }

    // Relationship to CargoBooking
    public function cargoBooking()
    {
        return $this->belongsTo(CargoBooking::class, 'booking_ref_no', 'booking_ref_no');
    }

    // App\Models\CargoReceipt.php

    public function cargoItem()
    {
        return $this->belongsTo(CargoItem::class, 'cargo_item_id', 'cargo_item_id');
    }

    // App\Models\CargoReceipt.php

    public function sender()
    {
        return $this->belongsTo(Sender::class, 'sender_id', 'sender_id');
    }

    public function consignee()
    {
        return $this->belongsTo(Consignee::class, 'consignee_id', 'consignee_id');
    }

    // App\Models\Booking.php

    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_ref_no', 'booking_ref_no');
    }



}
