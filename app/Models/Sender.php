<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
    use HasFactory;

    // Table name (optional if it matches plural of model)
    protected $table = 'sender';

    // Primary key
    protected $primaryKey = 'sender_id';

    // Allow mass assignment for these fields
    protected $fillable = [
        'sender_name',
        'sender_contactno',
        'sender_email',
        'sender_tin',
    ];
}
