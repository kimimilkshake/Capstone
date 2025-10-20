<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;
    protected $table = 'promo'; // explicitly define table

    protected $primaryKey = 'promo_id'; // primary key

    protected $fillable = [
        'promo_type',
        'promo_code',
        'promo_description',
        'promo_start_date',
        'promo_end_date',
        'promo_status',
        'promo_discount_rate',
    ];
}
