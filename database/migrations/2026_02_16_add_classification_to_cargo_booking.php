<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->unsignedBigInteger('cargo_classification_id')->nullable()->after('cargo_item_id');
            $table->unsignedBigInteger('measurement_unit_id')->nullable()->after('cargo_classification_id');
            $table->string('with_measurement')->nullable()->after('measurement_unit_id');

            // Foreign keys
            $table->foreign('cargo_classification_id')
                ->references('cargo_classification_id')
                ->on('cargo_classification')
                ->onDelete('restrict');

            $table->foreign('measurement_unit_id')
                ->references('measurement_unit_id')
                ->on('measurement_unit')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->dropForeign(['cargo_classification_id']);
            $table->dropForeign(['measurement_unit_id']);
            $table->dropColumn(['cargo_classification_id', 'measurement_unit_id', 'with_measurement']);
        });
    }
};
