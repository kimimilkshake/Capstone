<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteCode extends Model
{
    //
    use HasFactory;
    protected $table = 'route_code';

    protected $primaryKey = 'route_code_id';

    protected $fillable = [
        'route_code_name',
    ];

    // One route_code can have many route_ports
    public function routePorts()
    {
        return $this->hasMany(RoutePort::class, 'route_code_id', 'route_code_id');
    }

    public function cargoItems()
    {
        return $this->hasMany(CargoItem::class, 'route_code_id', 'route_code_id');
    }
}
