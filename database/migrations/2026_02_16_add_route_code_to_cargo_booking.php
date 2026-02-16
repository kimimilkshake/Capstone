<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->unsignedBigInteger('route_code_id')->nullable()->after('cargo_classification_id');

            // Foreign key
            $table->foreign('route_code_id')
                ->references('route_code_id')
                ->on('route_code')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->dropForeign(['route_code_id']);
            $table->dropColumn(['route_code_id']);
        });
    }
};
