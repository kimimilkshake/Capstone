<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    use HasFactory;

    protected $table = 'passenger';
    protected $primaryKey = 'passenger_id';

    protected $fillable = [
        'passenger_firstname',
        'passenger_midinitial',
        'passenger_lastname',
        'passenger_suffix',
        'passenger_age',
        'passenger_gender',
        'passenger_type',
        'passenger_address',
        'passenger_contactno',
        'passenger_email',
        'passenger_idnumber',
    ];
}
