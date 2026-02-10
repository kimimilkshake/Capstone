<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoBooking extends Model
{
    use HasFactory;

    protected $table = 'cargo_booking';
    protected $primaryKey = 'cargo_booking_id'; // Specify primary key
    public $incrementing = true; // if auto-increment
    protected $keyType = 'int'; // if integer

    protected $fillable = [
        'booking_ref_no',
        'cargo_item_id',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'cargo_picture',
        'freight',
        'arrastre',
        'total',
        'cbm',
        'rate',
        'value_per_item',
    ];
    

    // Relation to CargoItem
    public function cargoItem()
    {
        return $this->belongsTo(CargoItem::class, 'cargo_item_id', 'cargo_item_id');
    }
}
