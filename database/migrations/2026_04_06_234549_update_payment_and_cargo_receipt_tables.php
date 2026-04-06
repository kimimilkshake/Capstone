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
            $table->enum('payment_status', ['Pending', 'Initial', 'Completed', 'Canceled'])->default('Pending')->change();
        });

        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->decimal('arrastre', 10, 2)->default(0.00)->after('cargo_item_qty');
            $table->decimal('total', 10, 2)->default(0.00)->after('arrastre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->enum('payment_status', ['Pending', 'Completed', 'Canceled'])->default('Pending')->change();
        });

        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->dropColumn(['arrastre', 'total']);
        });
    }
};
