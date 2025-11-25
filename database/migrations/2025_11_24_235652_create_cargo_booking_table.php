<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_booking', function (Blueprint $table) {
            $table->id('cargo_booking_id'); // Primary key

            // Connect to booking
            $table->unsignedBigInteger('booking_ref_no');

            // Cargo item details
            $table->unsignedBigInteger('cargo_item_id'); // reference to cargo type
            $table->integer('quantity')->default(1);
            $table->decimal('weight', 8, 2);
            $table->decimal('length', 8, 2);
            $table->decimal('width', 8, 2);
            $table->decimal('height', 8, 2);
            $table->string('cargo_picture')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('booking_ref_no')
                ->references('booking_ref_no')
                ->on('booking')
                ->onDelete('cascade');

            $table->foreign('cargo_item_id')
                ->references('cargo_item_id')
                ->on('cargo_item')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_booking');
    }
};
