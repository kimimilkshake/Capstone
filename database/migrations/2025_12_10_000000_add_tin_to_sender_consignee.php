<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sender', function (Blueprint $table) {
            if (!Schema::hasColumn('sender', 'sender_tin')) {
                $table->string('sender_tin')->nullable()->after('sender_email');
            }
        });

        // Consignee TIN removed: only sender TIN is required per updated requirements
    }

    public function down(): void
    {
        Schema::table('sender', function (Blueprint $table) {
            if (Schema::hasColumn('sender', 'sender_tin')) {
                $table->dropColumn('sender_tin');
            }
        });
        // Consignee TIN removal not present (never added)
    }
};
