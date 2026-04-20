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
        Schema::create('pao_pow_data', function (Blueprint $table) {
            $table->id();
            $table->string('district');
            $table->integer('no_of_projects');
            $table->decimal('total_allocation', 15, 2);
            $table->integer('no_of_plans_received');
            $table->integer('no_of_project_estimate_received');
            $table->integer('pow_received');
            $table->integer('pow_approved');
            $table->integer('pow_submitted');
            $table->integer('ongoing_pow_preparation');
            $table->integer('pow_for_submission');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pao_pow_data');
    }
};
