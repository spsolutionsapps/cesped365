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
        Schema::table('garden_reports', function (Blueprint $table) {
            // Change growth_cm to allow NULL and have default value
            $table->decimal('growth_cm', 5, 2)->nullable()->default(0)->change();
            $table->decimal('growth_estimated', 5, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garden_reports', function (Blueprint $table) {
            $table->decimal('growth_cm', 5, 2)->nullable(false)->change();
            $table->decimal('growth_estimated', 5, 2)->nullable()->change();
        });
    }
};
