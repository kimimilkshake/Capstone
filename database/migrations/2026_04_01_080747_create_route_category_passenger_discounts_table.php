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
        Schema::create('route_category_passenger_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_category_id');
            $table->string('passenger_type');
            $table->decimal('discount_rate', 5, 2)->default(20);
            $table->timestamps();

            $table->foreign('route_category_id')
                ->references('route_category_id')
                ->on('route_category')
                ->onDelete('cascade');

            $table->unique(['route_category_id', 'passenger_type'], 'rc_passenger_discount_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_category_passenger_discounts');
    }
};
