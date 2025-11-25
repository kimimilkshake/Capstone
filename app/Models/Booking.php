<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'voyage_id',
        'sender_id',
        'consignee_id',
        'cargo_item_id',
        'cargo_item_qty',
    ];

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
}
