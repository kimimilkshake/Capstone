<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoCategory extends Model
{
    use HasFactory;

    protected $table = 'cargo_category';    protected $primaryKey = 'cargo_category_id';

    protected $fillable = [
        'cargo_category_name',
    ];

    public function cargoItems()
    {
        return $this->hasMany(CargoItem::class, 'cargo_category_id', 'cargo_category_id');
    }
}
