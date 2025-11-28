<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payment';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'booking_ref_no',
        'mode_of_payment',
        'payment_date',
        'total_amount',
        'payment_status',
        'transaction_code',
        'created_at',
        'updated_at',
    ];

}
