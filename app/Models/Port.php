<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $table = 'ports'; // optional if it follows convention
    protected $primaryKey = 'port_id'; 

    protected $fillable = [
        'terminal_name',
        'port_name',
        'city',
        'province',
    ];
}