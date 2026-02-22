<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoClassification extends Model
{
    use HasFactory;

    protected $table = 'cargo_classification';    protected $primaryKey = 'cargo_classification_id';

    protected $fillable = [
        'cargo_classification_name',
    ];
}
