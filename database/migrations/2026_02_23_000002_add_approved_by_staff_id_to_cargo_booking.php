<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            if (!Schema::hasColumn('cargo_booking', 'approved_by_staff_id')) {
                $table->unsignedBigInteger('approved_by_staff_id')->nullable()->after('booking_ref_no');
                $table->foreign('approved_by_staff_id')
                    ->references('staff_id')
                    ->on('staff')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            if (Schema::hasColumn('cargo_booking', 'approved_by_staff_id')) {
                $table->dropForeign(['approved_by_staff_id']);
                $table->dropColumn('approved_by_staff_id');
            }
        });
    }
};
