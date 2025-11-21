<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voyage extends Model
{
    use HasFactory;

    protected $table = 'voyage';
    protected $primaryKey = 'voyage_id';
    public $timestamps = false;

    protected $fillable = [
        'vessel_id',
        'route_port_id',
        'voyage_departure_date',
        'voyage_arrival_date',
        'voyage_estimated_TD',
        'voyage_estimated_TA',
        'voyage_actual_TD',
        'voyage_actual_TA',
        'voyage_status',
        'voyage_description',
        'voyage_code'
    ];

    // Each voyage belongs to a vessel
    public function vessel()
    {
        return $this->belongsTo(Vessel::class, 'vessel_id', 'vessel_id');
    }

    // Each voyage belongs to a route and port
    public function routePort()
    {
        return $this->belongsTo(RoutePort::class, 'route_port_id', 'route_port_id');
    }

    
}
