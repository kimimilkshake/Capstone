<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    //
    use HasFactory;

    protected $table = 'accommodation';
    protected $primaryKey = 'accommodation_id';

    protected $fillable = [
        'vessel_id',
        'accommodation_name',
        'accommodation_regular_price',
    ];

    public function vessel() {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }
}
