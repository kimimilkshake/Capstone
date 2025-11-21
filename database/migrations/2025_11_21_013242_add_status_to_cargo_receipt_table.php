<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])
                  ->default('Pending')
                  ->after('cargo_item_qty'); // adds it after the quantity column
        });
    }

    public function down(): void
    {
        Schema::table('cargo_receipt', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
