<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('events')
            ->whereNotNull('event_category_id')
            ->whereNotIn('event_category_id', function ($query) {
                $query->select('id')->from('event_categories');
            })
            ->delete();

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('event_category_id')
                ->references('id')
                ->on('event_categories')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['event_category_id']);
        });
    }
};
