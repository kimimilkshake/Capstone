<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoItem extends Model
{
    use HasFactory;

    protected $table = 'cargo_item';
    protected $primaryKey = 'cargo_item_id';

    protected $fillable = [
        'measurement_unit_id',
        'cargo_category_id',
        'route_category_id',
        'cargo_item_description',
        'cargo_item_freight',
        'cargo_item_measure_required',
        'cargo_item_min_length',
        'cargo_item_max_length',
        'cargo_item_min_width',
        'cargo_item_max_width',
        'cargo_item_min_height',
        'cargo_item_max_height',
    ];

    public function measurementUnit()
    {
        return $this->belongsTo(
            MeasurementUnit::class,
            'measurement_unit_id',
            'measurement_unit_id'
        );
    }

    public function cargo_category()
    {
        return $this->belongsTo(CargoCategory::class, 'cargo_category_id', 'cargo_category_id');
    }

    public function routeCategory()
    {
        return $this->belongsTo(
            RouteCategory::class,
            'route_category_id',
            'route_category_id'
        );
    }
}
