<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->unsignedBigInteger('cargo_booking_id')->nullable()->after('booking_ref_no');
            $table->foreign('cargo_booking_id')->references('cargo_booking_id')->on('cargo_booking')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->dropForeign(['cargo_booking_id']);
            $table->dropColumn('cargo_booking_id');
        });
    }
};
