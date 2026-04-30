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
        Schema::table('procurement_projects', function (Blueprint $table) {
            $table->date('ca_date')->nullable()->after('date_of_award');
            $table->string('ca_file')->nullable()->after('ca_date');
            $table->date('ntp_date')->nullable()->after('ca_file');
            $table->string('ntp_file')->nullable()->after('ntp_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_projects', function (Blueprint $table) {
            //
        });
    }
};
