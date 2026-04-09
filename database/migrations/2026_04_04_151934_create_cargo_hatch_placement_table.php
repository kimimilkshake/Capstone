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
        Schema::create('cargo_hatch_placement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voyage_id');
            $table->unsignedBigInteger('hatch_id');
            $table->unsignedBigInteger('cargo_receipt_id');
            $table->decimal('weight_kg', 10, 2)->default(0); // total weight from this receipt in this hatch
            $table->unsignedInteger('unit_count')->default(1);  // how many units landed in this hatch
            $table->timestamps();

            $table->unique(['voyage_id', 'hatch_id', 'cargo_receipt_id'], 'chp_unique');
            $table->index(['voyage_id', 'hatch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_hatch_placement');
    }
};
