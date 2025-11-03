<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $table = 'route';
    protected $primaryKey = 'route_id';
    public $timestamps = false;

    protected $fillable = [
        'route_origin',
        'route_destination',
    ];

    // One route can be used in many voyages
    public function voyages()
    {
        return $this->hasMany(Voyage::class, 'route_id', 'route_id');
    }
}
