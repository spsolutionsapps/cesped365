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
        Schema::create('garden_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->date('report_date');
            
            // General status
            $table->enum('general_status', ['good', 'regular', 'improve']);
            
            // Grass section
            $table->boolean('grass_even')->default(false);
            $table->enum('grass_color', ['ok', 'regular', 'bad']);
            $table->boolean('grass_spots')->default(false);
            $table->boolean('worn_areas')->default(false);
            $table->boolean('visible_weeds')->default(false);
            $table->text('grass_note')->nullable();
            
            // Growth section
            $table->decimal('growth_cm', 5, 2);
            $table->enum('growth_category', ['low', 'normal', 'high']);
            $table->decimal('growth_estimated', 5, 2)->nullable();
            $table->text('growth_note')->nullable();
            
            // Soil section
            $table->enum('soil_condition', ['loose', 'compact']);
            $table->boolean('aeration_recommended')->default(false);
            $table->text('soil_note')->nullable();
            
            // Humidity section
            $table->enum('humidity_status', ['dry', 'correct', 'excess']);
            $table->text('humidity_note')->nullable();
            
            // Pests section
            $table->enum('pests_status', ['none', 'mild', 'observe']);
            $table->text('pests_note')->nullable();
            
            // Flowerbeds section
            $table->enum('flowerbeds_status', ['clean', 'weeds', 'maintenance']);
            $table->text('flowerbeds_note')->nullable();
            
            // Recommendations
            $table->text('seasonal_recommendations')->nullable();
            $table->text('general_observations')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garden_reports');
    }
};

