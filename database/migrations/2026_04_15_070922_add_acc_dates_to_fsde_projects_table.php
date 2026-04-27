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
        Schema::table('fsde_projects', function (Blueprint $table) {
            // This will store the text you type, e.g., "April 2026"
            $table->string('acc_1_date')->nullable();
            $table->string('acc_2_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fsde_projects', function (Blueprint $table) {
            //
        });
    }
};
