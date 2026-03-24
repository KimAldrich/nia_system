<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ia_resolutions', function (Blueprint $table) {
            // Adds the status column and defaults it to 'not-validated'
            $table->string('status')->default('not-validated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ia_resolutions', function (Blueprint $table) {
            //
        });
    }
};
