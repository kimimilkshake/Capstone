<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consignee extends Model
{
    use HasFactory;

    protected $table = 'consignee';
    protected $primaryKey = 'consignee_id';

    protected $fillable = [
        'consignee_name',
        'consignee_contactno',
    ];
}
