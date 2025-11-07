<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $table = 'port';
    protected $primaryKey = 'port_id';
    public $timestamps = true;

    protected $fillable = [
        'port_name',
        'port_city',
        'port_province',
    ];

    // One port can be used in many voyages
    public function voyages()
    {
        return $this->hasMany(Voyage::class, 'port_id', 'port_id');
    }
}

