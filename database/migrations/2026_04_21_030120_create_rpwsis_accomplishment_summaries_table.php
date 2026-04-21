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
        Schema::create('rpwsis_accomplishment_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->string('plantation_type')->nullable();
            $table->string('year_established')->nullable();
            $table->string('target_area_1')->nullable();
            $table->string('area_planted')->nullable();
            $table->text('species_planted')->nullable(); 
            $table->string('spacing')->nullable();
            $table->string('maintenance')->nullable();
            $table->string('target_area_2')->nullable();
            $table->string('actual_area')->nullable();
            $table->string('mortality_rate')->nullable();
            $table->text('species_replanted')->nullable(); 
            $table->string('nis_name')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpwsis_accomplishment_summaries');
    }
};
