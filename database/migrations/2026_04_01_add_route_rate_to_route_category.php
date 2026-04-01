<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('route_category', function (Blueprint $table) {
            $table->decimal('route_rate', 5, 2)->default(0)->after('route_category_name');
        });
    }

    public function down(): void
    {
        Schema::table('route_category', function (Blueprint $table) {
            $table->dropColumn('route_rate');
        });
    }
};
