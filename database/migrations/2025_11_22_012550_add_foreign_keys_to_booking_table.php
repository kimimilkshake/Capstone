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
        Schema::table('booking', function (Blueprint $table) {
            // Add nullable foreign key columns
            $table->unsignedBigInteger('sender_id')->nullable()->after('booking_type');
            $table->unsignedBigInteger('consignee_id')->nullable()->after('sender_id');
            $table->unsignedBigInteger('cargo_item_id')->nullable()->after('consignee_id');
            $table->unsignedBigInteger('voyage_id')->nullable()->after('cargo_item_id');
            $table->unsignedBigInteger('payment_id')->nullable()->after('voyage_id');

            // Add foreign key constraints
            $table->foreign('sender_id')->references('sender_id')->on('sender')->onDelete('set null');
            $table->foreign('consignee_id')->references('consignee_id')->on('consignee')->onDelete('set null');
            $table->foreign('cargo_item_id')->references('cargo_item_id')->on('cargo_item')->onDelete('set null');
            $table->foreign('voyage_id')->references('voyage_id')->on('voyage')->onDelete('set null');
            $table->foreign('payment_id')->references('payment_id')->on('payment')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['consignee_id']);
            $table->dropForeign(['cargo_item_id']);
            $table->dropForeign(['voyage_id']);
            $table->dropForeign(['payment_id']);

            $table->dropColumn(['sender_id', 'consignee_id', 'cargo_item_id', 'voyage_id', 'payment_id']);
        });
    }
};
