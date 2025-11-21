<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoReceipt extends Model
{
    protected $table = 'cargo_receipt';
    protected $primaryKey = 'cargo_receipt_id';

    protected $fillable = [
        'booking_ref_no', 'sender_id', 'consignee_id',
        'cargo_item_id', 'voyage_id', 'cargo_item_qty'
    ];

    public function booking() {
        return $this->belongsTo(Booking::class, 'booking_ref_no', 'booking_ref_no');
    }
}
