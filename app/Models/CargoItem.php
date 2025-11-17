<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoItem extends Model
{
    use HasFactory;

    protected $table = 'cargo_item';
    protected $primaryKey = 'cargo_item_id';

    protected $fillable = [
        'cargo_item_classification',
        'cargo_item_description',
        'cargo_item_freight',
        'cargo_item_arrastre',
        'cargo_item_type',
        'cargo_item_volume',
        'cargo_item_weight',
        'cargo_item_length',
        'cargo_item_height',
        'cargo_item_width',
    ];
}
