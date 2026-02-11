<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillOfLading extends Model
{
    use HasFactory;

    protected $table = 'bill_of_lading';

    protected $primaryKey = 'bill_of_lading_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'cargo_receipt_id',
        'staff_id',
        'bl_date_issued',
        'bl_loading_port',
        'bl_unloading_port',
    ];

    protected $casts = [
        'bl_date_issued' => 'date',
    ];

    public function cargoReceipt()
    {
        return $this->belongsTo(CargoReceipt::class, 'cargo_receipt_id', 'cargo_receipt_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
