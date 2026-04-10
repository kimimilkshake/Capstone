<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Expand notification_type enum to include Cargo Payment and Cargo Verification
        DB::statement("ALTER TABLE notification MODIFY COLUMN notification_type ENUM('Cargo Booking Approval', 'Payment Received', 'Cargo Payment', 'Cargo Payment Verification')");

        // Expand notification_status enum to include Paid and Verified
        DB::statement("ALTER TABLE notification MODIFY COLUMN notification_status ENUM('Approved', 'Rejected', 'Read', 'Archived', 'Paid', 'Verified')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notification MODIFY COLUMN notification_type ENUM('Cargo Booking Approval', 'Payment Received')");
        DB::statement("ALTER TABLE notification MODIFY COLUMN notification_status ENUM('Approved', 'Rejected', 'Read', 'Archived')");
    }
};
