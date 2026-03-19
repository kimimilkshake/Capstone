<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE booking MODIFY booking_status ENUM('Confirmed', 'Pending', 'Canceled', 'Refunded', 'Boarded') NOT NULL");
    }

    public function down(): void
    {
        DB::table('booking')
            ->where('booking_status', 'Boarded')
            ->update(['booking_status' => 'Confirmed']);

        DB::statement("ALTER TABLE booking MODIFY booking_status ENUM('Confirmed', 'Pending', 'Canceled', 'Refunded') NOT NULL");
    }
};