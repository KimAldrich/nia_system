<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hydro_geo_projects', function (Blueprint $table) {
            $table->id();
            $table->string('year')->nullable();
            $table->string('district')->nullable();
            $table->string('project_code')->nullable();
            $table->string('system_name')->nullable();
            $table->text('description')->nullable();
            $table->string('municipality')->nullable();
            $table->string('status')->nullable();
            $table->string('result')->nullable(); // Feasible, Not Feasible, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hydro_geo_projects');
    }
};