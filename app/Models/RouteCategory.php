<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteCategory extends Model
{
    //
    use HasFactory;
    protected $table = 'route_category';
    protected $primaryKey = 'route_category_id';
    protected $fillable = [
        'route_category_name',
        'route_rate',
    ];

    public function routePorts()
    {
        return $this->hasMany(RoutePort::class, 'route_category_id', 'route_category_id');
    }

    public function cargoItems()
    {
        return $this->hasMany(CargoItem::class, 'route_category_id', 'route_category_id');
    }

    public function passengerDiscounts()
    {
        return $this->hasMany(RouteCategoryDiscount::class, 'route_category_id', 'route_category_id');
    }
}
