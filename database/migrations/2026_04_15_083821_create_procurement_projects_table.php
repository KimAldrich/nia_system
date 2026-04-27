<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('procurement_projects', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable(); // Stores the category like "Repair of National Irrigation Systems"
            $table->string('proj_no')->nullable();
            $table->text('name_of_project')->nullable();
            $table->string('municipality')->nullable();
            $table->string('allocation')->nullable();
            $table->string('abc')->nullable();
            $table->string('bid_out')->nullable();
            $table->string('for_bidding')->nullable();
            $table->string('date_of_bidding')->nullable();
            $table->string('awarded')->nullable();
            $table->string('date_of_award')->nullable();
            $table->string('contract_no')->nullable();
            $table->string('contract_amount')->nullable();
            $table->string('name_of_contractor')->nullable();
            $table->text('remarks')->nullable();
            $table->text('project_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_projects');
    }
};
