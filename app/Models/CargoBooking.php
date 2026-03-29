<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Staff;

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
        'cargo_classification_id',
        'route_category_id',
        'measurement_unit_id',
        'with_measurement',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'cargo_picture',
        'freight',
        'total',
        'cbm',
        'approved_by_staff_id',
    ];
    

    // Relation to CargoItem
    public function cargoItem()
    {
        return $this->belongsTo(CargoItem::class, 'cargo_item_id', 'cargo_item_id');
    }

    public function cargoClassification()
    {
        return $this->belongsTo(CargoClassification::class, 'cargo_classification_id', 'cargo_classification_id');
    }

    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit_id', 'measurement_unit_id');
    }

    public function routeCategory()
    {
        return $this->belongsTo(RouteCategory::class, 'route_category_id', 'route_category_id');
    }

    public function approvedByStaff()
    {
        return $this->belongsTo(Staff::class, 'approved_by_staff_id', 'staff_id');
    }
}
