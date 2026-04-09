<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->foreignId('cargo_receipt_id')->nullable()->constrained('cargo_receipt', 'cargo_receipt_id')->after('booking_ref_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->dropForeign(['cargo_receipt_id']);
            $table->dropColumn('cargo_receipt_id');
        });
    }
};
