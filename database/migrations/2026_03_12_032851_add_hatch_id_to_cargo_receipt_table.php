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
        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->unsignedBigInteger('hatch_id')
                ->nullable()
                ->after('cargo_booking_id');
            $table->foreign('hatch_id')
                ->references('hatch_id')
                ->on('hatch')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->dropForeign(['hatch_id']);
            $table->dropColumn('hatch_id');
        });
    }
};
