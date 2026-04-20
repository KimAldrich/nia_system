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
            // We already have jan_phy, jan_fin, feb_phy, feb_fin from earlier. 
            // Let's add the rest of the year!
            $months = ['mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

            foreach ($months as $m) {
                $table->string($m . '_phy')->nullable();
                $table->string($m . '_fin')->nullable();
            }
            $table->string('acc_year')->nullable(); // To store the '2026' part
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
