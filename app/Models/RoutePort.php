<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePort extends Model
{
    protected $table = 'route_port';
    protected $primaryKey = 'route_port_id';
    protected $fillable = [
        'route_category_id',
        'route_code',
        'route_origin',
        'route_destination',
        'port_origin_name',
        'port_origin_city',
        'port_origin_province',
        'port_destination_name',
        'port_destination_city',
        'port_destination_province',
    ];

    // One route_port can be used in many voyages
    public function voyages()
    {
        return $this->hasMany(Voyage::class, 'route_port_id', 'route_port_id');
    }

    public function cargoItems()
    {
        return $this->hasMany(CargoItem::class, 'route_port_id', 'route_port_id');
    }

    public function routeCategory()
    {
        return $this->belongsTo(RouteCategory::class, 'route_category_id', 'route_category_id');
    }

}
