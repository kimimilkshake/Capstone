<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'admin_id',
        'staff_name',
        'staff_user',
        'staff_password',
        'staff_dob',
        'staff_gender',
        'staff_email',
        'staff_status',
    ];
    protected $hidden = ['staff_password'];
}
