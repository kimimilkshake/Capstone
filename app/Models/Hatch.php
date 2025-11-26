<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hatch extends Model
{
    use HasFactory;

    protected $table = 'hatch';
    protected $primaryKey = 'hatch_id';

    protected $fillable = [
        'vessel_id',
        'hatch_label',
        'hatch_length',
        'hatch_width',
        'hatch_height',
        'hatch_weight_capacity',
        'hatch_area_capacity',
        'hatch_capacity_per_hold',
    ];

    protected $casts = [
        'hatch_length' => 'decimal:2',
        'hatch_width' => 'decimal:2',
        'hatch_height' => 'decimal:2',
        'hatch_weight_capacity' => 'decimal:2',
        'hatch_area_capacity' => 'decimal:2',
        'hatch_capacity_per_hold' => 'decimal:2',
    ];

    public function vessel() {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }
}
