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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref_no')->index();
            $table->unsignedBigInteger('passenger_id')->index();
            $table->string('qr_data'); // e.g., "7:7" (booking_ref:passenger_id)
            $table->string('qr_code_path'); // File path to saved PNG
            $table->timestamps();

            // Unique constraint - one QR per booking per passenger
            $table->unique(['booking_ref_no', 'passenger_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
