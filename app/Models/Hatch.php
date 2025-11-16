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
        'hatch_area_capacity',
        'hatch_weight_capacity',
    ];

    public function vessel() {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }
}
