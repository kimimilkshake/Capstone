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
        'cargo_item_classification',
        'cargo_item_description',
        'cargo_item_freight',
        'cargo_item_arrastre',
        'route_port_id',
    ];

    public function routePort()
    {
        return $this->belongsTo(RoutePort::class, 'route_port_id', 'route_port_id');
    }
}
