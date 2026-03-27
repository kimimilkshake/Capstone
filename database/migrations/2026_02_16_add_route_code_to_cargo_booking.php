<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->unsignedBigInteger('route_category_id')->nullable()->after('cargo_classification_id');

            // Foreign key
            $table->foreign('route_category_id')
                ->references('route_category_id')
                ->on('route_category')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->dropForeign(['route_category_id']);
            $table->dropColumn(['route_category_id']);
        });
    }
};
