<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeasurementUnit extends Model
{
    use HasFactory;
    protected $table = 'measurement_unit';
    protected $primaryKey = 'measurement_unit_id';
    protected $fillable = [
        'measurement_unit_name',
        'measurement_unit_abbreviation',
    ];
    public function cargoItems()
    {
        return $this->hasMany(CargoItem::class, 'measurement_unit_id', 'measurement_unit_id');
    }
}
