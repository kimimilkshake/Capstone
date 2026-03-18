<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $table = 'qr_codes';

    protected $fillable = [
        'booking_ref_no',
        'passenger_id',
        'qr_data',
        'qr_code_path'
    ];
}
