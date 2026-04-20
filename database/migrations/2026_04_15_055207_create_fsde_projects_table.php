<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fsde_projects', function (Blueprint $table) {
            $table->id();
            $table->string('year')->nullable();
            $table->text('project_name')->nullable();
            $table->string('municipality')->nullable();
            $table->string('type_of_study')->nullable();
            $table->string('budget')->nullable();
            $table->text('consultant')->nullable();

            // 🌟 ALL YOUR MISSING COLUMNS ADDED HERE 🌟
            $table->string('period_start')->nullable();
            $table->string('period_end')->nullable();
            $table->string('contract_amount')->nullable();
            $table->string('actual_obligation')->nullable();
            $table->string('value_of_acc')->nullable();
            $table->string('actual_expenditures')->nullable();
            $table->string('jan_phy')->nullable();
            $table->string('jan_fin')->nullable();
            $table->string('feb_phy')->nullable();
            $table->string('feb_fin')->nullable();

            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fsde_projects');
    }
};