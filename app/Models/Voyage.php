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
        'route_id',
        'port_id',
        'voyage_departure_date',
        'voyage_arrival_date',
        'voyage_estimated_TD',
        'voyage_estimated_TA',
        'status',
        'voyage_code'
    ];

    // Each voyage belongs to a vessel
    public function vessel()
    {
        return $this->belongsTo(Vessel::class, 'vessel_id', 'vessel_id');
    }

    // Each voyage belongs to a route
    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id', 'route_id');
    }

    // Each voyage belongs to a port
    public function port()
    {
        return $this->belongsTo(Port::class, 'port_id', 'port_id');
    }
}
