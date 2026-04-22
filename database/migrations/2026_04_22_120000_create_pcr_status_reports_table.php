<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pcr_status_reports', function (Blueprint $table) {
            $table->id();
            $table->string('fund_source', 50);
            $table->unsignedInteger('no_of_contracts');
            $table->decimal('allocation', 15, 2)->default(0);
            $table->unsignedInteger('no_of_pcr_prepared');
            $table->unsignedInteger('no_of_pcr_submitted_to_regional_office');
            $table->decimal('accomplishment_percentage', 5, 2)->default(0);
            $table->unsignedInteger('for_signing_of_ia_chief_dm_rm')->default(0);
            $table->unsignedInteger('for_submission_to_ro1')->default(0);
            $table->unsignedInteger('not_yet_prepared_pending_details')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pcr_status_reports');
    }
};
