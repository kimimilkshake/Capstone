<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vessel extends Model
{
    //
    use HasFactory;

    protected $table = 'vessel';
    protected $primaryKey = 'vessel_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'admin_id',
        'vessel_code',
        'vessel_name',
        'vessel_total_passenger_capacity',
        'vessel_cot_plan_url',
        'vessel_status',
    ];

    // Relationships
    public function accommodations() {
        return $this->hasMany(Accommodation::class, 'vessel_id');
    }

    public function hatches() {
        return $this->hasMany(Hatch::class, 'vessel_id');
    }

    public function getTotalPassengerCapacity()
    {
        return $this->accommodations()->sum('accommodation_capacity');
    }

}
