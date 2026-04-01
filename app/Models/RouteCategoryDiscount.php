<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteCategoryDiscount extends Model
{
    protected $table = 'route_category_passenger_discounts';

    protected $fillable = ['route_category_id', 'passenger_type', 'discount_rate'];

    public function routeCategory()
    {
        return $this->belongsTo(RouteCategory::class, 'route_category_id', 'route_category_id');
    }
}
